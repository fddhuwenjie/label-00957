<?php
namespace app\controller\api;

use app\controller\BaseController;
use app\model\User;
use app\model\Favorite;
use app\model\PlayHistory;
use think\facade\Log;

/**
 * 用户API控制器
 */
class UserController extends BaseController
{
    /**
     * 获取用户信息
     */
    public function profile()
    {
        $userId = $this->request->userId;
        $user = User::find($userId);
        
        if (!$user) {
            return error('用户不存在', 404);
        }

        return success($user);
    }

    /**
     * 更新用户信息
     */
    public function updateProfile()
    {
        $userId = $this->request->userId;
        $data = input('post.');

        $user = User::find($userId);
        if (!$user) {
            return error('用户不存在', 404);
        }

        $allowFields = ['nickname', 'avatar'];
        $updateData = array_intersect_key($data, array_flip($allowFields));

        $user->save($updateData);

        Log::info('用户更新资料', ['user_id' => $userId]);

        return success($user, '更新成功');
    }

    /**
     * 收藏列表
     */
    public function favorites()
    {
        $userId = $this->request->userId;
        $page = input('page', 1, 'intval');
        $limit = input('limit', 20, 'intval');

        $list = Favorite::where('user_id', $userId)
            ->with(['music', 'music.category'])
            ->order('created_at', 'desc')
            ->paginate([
                'page' => $page,
                'list_rows' => $limit
            ]);

        return success($list);
    }

    /**
     * 添加收藏
     */
    public function addFavorite()
    {
        $userId = $this->request->userId;
        $musicId = input('post.music_id', 0, 'intval');

        if ($musicId <= 0) {
            return error('参数错误', 422);
        }

        $exists = Favorite::where('user_id', $userId)
            ->where('music_id', $musicId)
            ->find();

        if ($exists) {
            return error('已收藏过该音乐', 400);
        }

        Favorite::create([
            'user_id' => $userId,
            'music_id' => $musicId
        ]);

        Log::info('添加收藏', ['user_id' => $userId, 'music_id' => $musicId]);

        return success(null, '收藏成功');
    }

    /**
     * 取消收藏
     */
    public function removeFavorite()
    {
        $userId = $this->request->userId;
        $musicId = input('id', 0, 'intval');

        Favorite::where('user_id', $userId)
            ->where('music_id', $musicId)
            ->delete();

        Log::info('取消收藏', ['user_id' => $userId, 'music_id' => $musicId]);

        return success(null, '已取消收藏');
    }

    /**
     * 播放历史
     */
    public function history()
    {
        $userId = $this->request->userId;
        $page = input('page', 1, 'intval');
        $limit = input('limit', 20, 'intval');

        $list = PlayHistory::where('user_id', $userId)
            ->with(['music', 'music.category'])
            ->order('played_at', 'desc')
            ->paginate([
                'page' => $page,
                'list_rows' => $limit
            ]);

        return success($list);
    }
}
