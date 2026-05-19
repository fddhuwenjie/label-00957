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
