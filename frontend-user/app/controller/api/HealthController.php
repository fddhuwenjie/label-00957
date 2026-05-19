<?php
namespace app\controller\api;

use app\controller\BaseController;
use think\facade\Db;
use think\facade\Cache;
use think\facade\Log;

class HealthController extends BaseController
{
    public function check()
    {
        $checks = [];
        $overallStatus = 'healthy';

        $checks['database'] = $this->checkDatabase();
        $checks['redis'] = $this->checkRedis();
        $checks['disk'] = $this->checkDiskSpace();

        foreach ($checks as $check) {
            if ($check['status'] !== 'ok') {
                $overallStatus = 'unhealthy';
                break;
            }
        }

        $httpStatus = $overallStatus === 'healthy' ? 200 : 503;

        return json([
            'status' => $overallStatus,
            'timestamp' => date('c'),
            'checks' => $checks,
        ], $httpStatus);
    }

    private function checkDatabase(): array
    {
        try {
            Db::query('SELECT 1');
            return [
                'status' => 'ok',
                'message' => 'Database connection is active',
            ];
        } catch (\Exception $e) {
            Log::error('Health check: database connection failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }

    private function checkRedis(): array
    {
        try {
            $cacheConfig = config('cache');
            $driver = $cacheConfig['default'] ?? 'file';

            if ($driver !== 'redis') {
                $testKey = 'health_check_' . time();
                Cache::set($testKey, 'ok', 10);
                $result = Cache::get($testKey);
                Cache::delete($testKey);

                if ($result === 'ok') {
                    return [
                        'status' => 'ok',
                        'message' => 'Cache (file driver) is working',
                        'driver' => $driver,
                    ];
                }

                return [
                    'status' => 'error',
                    'message' => 'Cache read/write failed',
                    'driver' => $driver,
                ];
            }

            $redis = new \Redis();
            $host = getenv('REDIS_HOST') ?: '127.0.0.1';
            $port = (int)(getenv('REDIS_PORT') ?: 6379);
            $password = getenv('REDIS_PASSWORD') ?: '';

            $connected = $redis->connect($host, $port, 2);
            if (!$connected) {
                return [
                    'status' => 'error',
                    'message' => 'Redis connection failed',
                ];
            }

            if ($password && !$redis->auth($password)) {
                return [
                    'status' => 'error',
                    'message' => 'Redis authentication failed',
                ];
            }

            $redis->ping();
            $redis->close();

            return [
                'status' => 'ok',
                'message' => 'Redis connection is active',
            ];
        } catch (\Exception $e) {
            Log::error('Health check: Redis/cache check failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'error',
                'message' => 'Redis/Cache check failed: ' . $e->getMessage(),
            ];
        }
    }

    private function checkDiskSpace(): array
    {
        $path = runtime_path();
        $freeBytes = disk_free_space($path);
        $totalBytes = disk_total_space($path);

        if ($freeBytes === false || $totalBytes === false) {
            return [
                'status' => 'error',
                'message' => 'Unable to determine disk space',
            ];
        }

        $usedPercent = round(($totalBytes - $freeBytes) / $totalBytes * 100, 2);
        $freeGB = round($freeBytes / 1024 / 1024 / 1024, 2);
        $totalGB = round($totalBytes / 1024 / 1024 / 1024, 2);

        $status = 'ok';
        if ($usedPercent > 90) {
            $status = 'error';
        } elseif ($usedPercent > 80) {
            $status = 'warning';
        }

        return [
            'status' => $status,
            'free' => $freeGB . ' GB',
            'total' => $totalGB . ' GB',
            'used_percent' => $usedPercent . '%',
        ];
    }
}
