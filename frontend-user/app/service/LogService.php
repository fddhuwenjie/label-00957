<?php
namespace app\service;

use think\facade\Log;
use think\facade\Db;

/**
 * 用户操作日志服务
 */
class LogService
{
    /**
     * 记录用户操作日志
     */
    public static function record(int $userId, string $action, string $content = ''): void
    {
        try {
            Db::name('user_log')->insert([
                'user_id' => $userId,
                'action' => $action,
                'content' => $content,
                'ip' => request()->ip(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            Log::error('记录用户日志失败: ' . $e->getMessage());
        }
    }
}
