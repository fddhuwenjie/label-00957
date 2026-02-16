<?php
namespace app\controller\api;

use app\controller\BaseController;
use app\model\User;
use app\service\LogService;

class UserController extends BaseController
{
    public function list()
    {
        $page = input('page', 1, 'intval');
        $limit = input('limit', 20, 'intval');
        $keyword = input('keyword', '', 'trim');
        $status = input('status', -1, 'intval');

        $query = User::field('id, email, nickname, avatar, status, created_at');

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->whereLike('email', "%{$keyword}%")
                  ->whereOr('nickname', 'like', "%{$keyword}%");
            });
        }

        if ($status >= 0) {
            $query->where('status', $status);
        }

        $list = $query->order('id', 'desc')
            ->paginate(['page' => $page, 'list_rows' => $limit]);

        return success($list);
    }

    public function updateStatus($id)
    {
        $id = intval($id);
        $status = input('status', 0, 'intval');

        $user = User::find($id);
        if (!$user) {
            return error('用户不存在', 404);
        }

        $user->status = $status;
        $user->save();

        $action = $status === 1 ? '启用' : '禁用';
        LogService::record($this->request->adminId, '用户管理', $action, "{$action}用户: {$user->email}");

        return success(null, '操作成功');
    }
}
