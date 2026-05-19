<?php
namespace app\controller;

use think\facade\Db;

class HealthController extends BaseController
{
    public function check()
    {
        $status = 'ok';
        $checks = [];

        try {
            Db::query('SELECT 1');
            $checks['database'] = ['status' => 'ok'];
        } catch (\Exception $e) {
            $status = 'degraded';
            $checks['database'] = ['status' => 'error', 'message' => $e->getMessage()];
        }

        try {
            $redis = new \Redis();
            if ($redis->connect(env('REDIS_HOST', '127.0.0.1'), (int) env('REDIS_PORT', 6379), 1)) {
                if (env('REDIS_PASSWORD')) {
                    $redis->auth(env('REDIS_PASSWORD'));
                }
                $redis->ping();
                $checks['redis'] = ['status' => 'ok'];
                $redis->close();
            } else {
                $checks['redis'] = ['status' => 'skipped', 'message' => 'Redis not configured'];
            }
        } catch (\Exception $e) {
            $status = 'degraded';
            $checks['redis'] = ['status' => 'error', 'message' => $e->getMessage()];
        }

        try {
            $diskTotal = disk_total_space('/');
            $diskFree = disk_free_space('/');
            $usagePercent = $diskTotal > 0 ? ((($diskTotal - $diskFree) / $diskTotal) * 100) : 0;
            $healthy = $usagePercent < 90;
            if (!$healthy && $status === 'ok') {
                $status = 'warning';
            }
            $checks['disk'] = [
                'status' => $healthy ? 'ok' : 'warning',
                'usage_percent' => round($usagePercent, 2),
                'free_bytes' => $diskFree,
                'total_bytes' => $diskTotal,
            ];
        } catch (\Exception $e) {
            $status = 'degraded';
            $checks['disk'] = ['status' => 'error', 'message' => $e->getMessage()];
        }

        return json([
            'status' => $status,
            'timestamp' => date('c'),
            'checks' => $checks,
        ], $status === 'ok' ? 200 : 503);
    }
}
