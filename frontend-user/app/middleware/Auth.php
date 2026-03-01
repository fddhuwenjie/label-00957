<?php
namespace app\middleware;

use Closure;
use think\Request;
use think\Response;
use app\service\JwtService;
use app\model\User;

/**
 * 认证中间件
 */
class Auth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Authorization', '');
        $token = str_replace('Bearer ', '', $token);

        if (empty($token)) {
            return error('请先登录', 401);
        }

        try {
            $payload = JwtService::verify($token);
            $userId = $payload['uid'];
        } catch (\Exception $e) {
            return error('登录已过期，请重新登录', 401);
        }

        $user = User::find($userId);
        if (!$user || $user->status !== 1) {
            return error('账号已被禁用，请联系管理员', 401);
        }

        $request->userId = $userId;

        return $next($request);
    }
}
