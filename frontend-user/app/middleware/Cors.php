<?php
namespace app\middleware;

use Closure;
use think\Request;
use think\Response;

/**
 * 跨域中间件
 */
class Cors
{
    public function handle(Request $request, Closure $next): Response
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Max-Age: 86400');

        if ($request->isOptions()) {
            return response('', 204);
        }

        return $next($request);
    }
}
