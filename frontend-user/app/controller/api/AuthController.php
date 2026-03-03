<?php
namespace app\controller\api;

use app\controller\BaseController;
use app\model\User;
use app\service\JwtService;
use app\service\LogService;
use app\service\CryptoService;
use app\validate\UserValidate;
use think\exception\ValidateException;
use think\facade\Log;

/**
 * 认证API控制器
 */
class AuthController extends BaseController
{
    /**
     * 获取公钥
     */
    public function publicKey()
    {
        $publicKey = CryptoService::getPublicKey();
        return success(['public_key' => $publicKey]);
    }

    /**
     * 用户注册
     */
    public function register()
    {
        $data = input('post.');
        
        // 尝试解密密码（如果解密失败则认为是明文密码）
        if (!empty($data['password'])) {
            try {
                $data['password'] = CryptoService::decrypt($data['password']);
            } catch (\Exception $e) {
                // 解密失败，使用原始密码（可能是明文）
                Log::warning('密码解密失败，使用明文密码');
            }
        }
        
        try {
            validate(UserValidate::class)->scene('register')->check($data);
        } catch (ValidateException $e) {
            return error($e->getError(), 422);
        }

        $user = User::create([
            'email' => $data['email'],
            'password' => $data['password'],
            'nickname' => $data['nickname'] ?? explode('@', $data['email'])[0],
        ]);

        Log::info('用户注册成功', ['user_id' => $user->id, 'email' => $data['email']]);
        LogService::record($user->id, '注册', '用户注册账号');

        $token = JwtService::generate($user->id);

        return success([
            'token' => $token,
            'user' => $user->hidden(['password'])->toArray()
        ], '注册成功');
    }

    /**
     * 用户登录
     */
    public function login()
    {
        // 兼容 JSON 请求体（param 已合并 post/put）
        $data = request()->param();
        if (empty($data['email']) && empty($data['password'])) {
            $raw = json_decode(request()->getContent(), true);
            if (is_array($raw)) {
                $data = $raw;
            }
        }
        
        // 兼容 username 字段名
        if (!isset($data['email']) && isset($data['username'])) {
            $data['email'] = $data['username'];
        }

        // 基本验证
        if (empty($data['email'])) {
            return error('邮箱不能为空', 422);
        }
        if (empty($data['password'])) {
            return error('密码不能为空', 422);
        }

        // 尝试解密密码（如果解密失败则认为是明文密码）
        $password = (string) ($data['password'] ?? '');
        try {
            $decrypted = CryptoService::decrypt($data['password']);
            $password = $decrypted;
        } catch (\Exception $e) {
            // 解密失败，使用原始密码（可能是明文）
            Log::warning('密码解密失败，使用明文密码', ['email' => $data['email']]);
        }

        $user = User::where('email', trim($data['email']))->find();
        
        if (!$user || !$user->verifyPassword((string) $password)) {
            return error('邮箱或密码错误', 400);
        }

        if ($user->status !== 1) {
            return error('账号已被禁用', 403);
        }

        Log::info('用户登录成功', ['user_id' => $user->id]);
        LogService::record($user->id, '登录', '用户登录系统');

        $token = JwtService::generate($user->id);

        return success([
            'token' => $token,
            'user' => $user->hidden(['password'])->toArray()
        ], '登录成功');
    }

    /**
     * 用户退出
     */
    public function logout()
    {
        $userId = $this->request->userId ?? 0;
        if ($userId > 0) {
            LogService::record($userId, '退出', '用户退出系统');
        }
        return success(null, '退出成功');
    }
}
