<?php
namespace app\middleware;

use Closure;
use think\facade\Cache;
use think\Request;
use think\Response;

class RateLimit
{
    public function handle(Request $request, Closure $next, int $limit = 60, int $window = 60): Response
    {
        $ip = $request->ip();
        $endpoint = $request->path();
        $key = 'rate_limit:' . $ip . ':' . md5($endpoint);

        $count = Cache::get($key, 0);

        if ($count >= $limit) {
            $response = error('Rate limit exceeded', 429);
            $response->header('X-RateLimit-Limit', $limit);
            $response->header('X-RateLimit-Remaining', 0);
            $response->header('X-RateLimit-Reset', time() + $window);
            return $response;
        }

        Cache::inc($key);
        if ($count === 0) {
            Cache::set($key, 1, $window);
        }

        $response = $next($request);
        $response->header('X-RateLimit-Limit', $limit);
        $response->header('X-RateLimit-Remaining', max(0, $limit - $count - 1));
        $response->header('X-RateLimit-Reset', time() + $window);

        return $response;
    }
}
