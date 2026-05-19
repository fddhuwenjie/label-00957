<?php

namespace app\controller\api;

use app\controller\BaseController;
use think\facade\Cache;
use think\facade\Db;
use think\Response;

class HealthController extends BaseController
{
    public function check(): Response
    {
        $status = [
            'status' => 'healthy',
            'timestamp' => time(),
            'services' => [
                'database' => $this->checkDatabase(),
                'redis' => $this->checkRedis(),
                'disk' => $this->checkDiskSpace()
            ]
        ];

        $allHealthy = true;
        foreach ($status['services'] as $service) {
            if ($service['status'] !== 'healthy') {
                $allHealthy = false;
                break;
            }
        }

        $status['status'] = $allHealthy ? 'healthy' : 'unhealthy';
        $httpCode = $allHealthy ? 200 : 503;

        return json($status, $httpCode);
    }

    private function checkDatabase(): array
    {
        try {
            Db::connect()->query('SELECT 1');
            return [
                'status' => 'healthy',
                'message' => 'Database connection is working'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => $e->getMessage()
            ];
        }
    }

    private function checkRedis(): array
    {
        try {
            $cacheDriver = Cache::store('redis');
            $testKey = 'health_check_' . time();
            $cacheDriver->set($testKey, 'ok', 10);
            $result = $cacheDriver->get($testKey);

            if ($result === 'ok') {
                return [
                    'status' => 'healthy',
                    'message' => 'Redis connection is working'
                ];
            }

            return [
                'status' => 'degraded',
                'message' => 'Redis connection is working but value mismatch'
            ];
        } catch (\Exception $e) {
            try {
                Cache::store('file')->set($testKey, 'ok', 10);
                return [
                    'status' => 'degraded',
                    'message' => 'Redis unavailable, using file cache fallback: ' . $e->getMessage()
                ];
            } catch (\Exception $e2) {
                return [
                    'status' => 'unhealthy',
                    'message' => 'All cache drivers failed: ' . $e->getMessage()
                ];
            }
        }
    }

    private function checkDiskSpace(): array
    {
        $diskPath = app()->getRootPath();
        $freeSpace = @disk_free_space($diskPath);
        $totalSpace = @disk_total_space($diskPath);

        if ($freeSpace === false || $totalSpace === false) {
            return [
                'status' => 'unknown',
                'message' => 'Unable to check disk space'
            ];
        }

        $freePercent = ($freeSpace / $totalSpace) * 100;
        $freeGB = round($freeSpace / (1024 * 1024 * 1024), 2);
        $totalGB = round($totalSpace / (1024 * 1024 * 1024), 2);

        if ($freePercent < 5) {
            return [
                'status' => 'critical',
                'message' => sprintf(
                    'Disk space critically low: %.2f GB free of %.2f GB (%.1f%%)',
                    $freeGB,
                    $totalGB,
                    $freePercent
                ),
                'free_gb' => $freeGB,
                'total_gb' => $totalGB,
                'free_percent' => $freePercent
            ];
        }

        if ($freePercent < 10) {
            return [
                'status' => 'warning',
                'message' => sprintf(
                    'Disk space low: %.2f GB free of %.2f GB (%.1f%%)',
                    $freeGB,
                    $totalGB,
                    $freePercent
                ),
                'free_gb' => $freeGB,
                'total_gb' => $totalGB,
                'free_percent' => $freePercent
            ];
        }

        return [
            'status' => 'healthy',
            'message' => sprintf(
                'Disk space sufficient: %.2f GB free of %.2f GB (%.1f%%)',
                $freeGB,
                $totalGB,
                $freePercent
            ),
            'free_gb' => $freeGB,
            'total_gb' => $totalGB,
            'free_percent' => $freePercent
        ];
    }
}
