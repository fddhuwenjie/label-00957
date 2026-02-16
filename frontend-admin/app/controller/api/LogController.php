<?php
namespace app\controller\api;

use app\controller\BaseController;
use app\model\OperationLog;

/**
 * 操作日志控制器
 */
class LogController extends BaseController
{
    /**
     * 日志列表
     */
    public function list()
    {
        $page = input('page', 1, 'intval');
        $limit = input('limit', 50, 'intval');
        $module = input('module', '', 'trim');
        $adminId = input('admin_id', 0, 'intval');

        $query = OperationLog::with(['admin']);

        if ($module) {
            $query->where('module', $module);
        }

        if ($adminId > 0) {
            $query->where('admin_id', $adminId);
        }

        $list = $query->order('created_at', 'desc')
            ->paginate(['page' => $page, 'list_rows' => $limit]);

        return success($list);
    }
}
