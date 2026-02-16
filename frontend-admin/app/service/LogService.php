<?php
namespace app\service;

use app\model\OperationLog;
use think\facade\Log;

/**
 * 操作日志服务
 */
class LogService
{
    /**
     * 记录操作日志
     */
    public static function record(int $adminId, string $module, string $action, string $content = ''): void
    {
        try {
            OperationLog::create([
                'admin_id' => $adminId,
                'module' => $module,
                'action' => $action,
                'content' => $content,
                'ip' => request()->ip()
            ]);
        } catch (\Exception $e) {
            Log::error('记录操作日志失败: ' . $e->getMessage());
        }
    }
}
