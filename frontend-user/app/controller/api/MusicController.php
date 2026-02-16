<?php
namespace app\controller\api;

use app\controller\BaseController;
use app\model\Music;
use app\model\Category;
use app\model\PlayHistory;
use think\facade\Log;

/**
 * 音乐API控制器
 */
class MusicController extends BaseController
{
    /**
     * 音乐列表
     */
    public function list()
    {
        $page = input('page', 1, 'intval');
        $limit = input('limit', 20, 'intval');
        $categoryId = input('category_id', 0, 'intval');

        $query = Music::where('status', 1);
        
        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }

        $list = $query->with(['category'])
            ->order('created_at', 'desc')
            ->paginate([
                'page' => $page,
                'list_rows' => $limit
            ]);

        return success($list);
    }

    /**
     * 音乐详情
     */
    public function detail()
    {
        $id = input('id', 0, 'intval');
        
        $music = Music::with(['category'])->find($id);
        
        if (!$music || $music->status !== 1) {
            return error('音乐不存在', 404);
        }

        return success($music);
    }

    /**
     * 搜索音乐
     */
    public function search()
    {
        $keyword = input('keyword', '', 'trim');
        $page = input('page', 1, 'intval');
        $limit = input('limit', 20, 'intval');

        if (empty($keyword)) {
            return error('请输入搜索关键词', 422);
        }

        $list = Music::where('status', 1)
            ->where(function($query) use ($keyword) {
                $query->whereLike('title', "%{$keyword}%")
                    ->whereOr('artist', 'like', "%{$keyword}%")
                    ->whereOr('album', 'like', "%{$keyword}%");
            })
            ->with(['category'])
            ->order('play_count', 'desc')
            ->paginate([
                'page' => $page,
                'list_rows' => $limit
            ]);

        return success($list);
    }

    /**
     * 推荐音乐
     */
    public function recommend()
    {
        $limit = input('limit', 10, 'intval');

        $list = Music::where('status', 1)
            ->with(['category'])
            ->orderRaw('RAND()')
            ->limit($limit)
            ->select();

        return success($list);
    }

    /**
     * 排行榜
     */
    public function ranking()
    {
        $type = input('type', 'hot'); // hot, new
        $limit = input('limit', 50, 'intval');

        $query = Music::where('status', 1)->with(['category']);

        if ($type === 'new') {
            $query->order('created_at', 'desc');
        } else {
            $query->order('play_count', 'desc');
        }

        $list = $query->limit($limit)->select();

        return success($list);
    }

    /**
     * 分类列表
     */
    public function categories()
    {
        $list = Category::where('status', 1)
            ->order('sort_order', 'asc')
            ->select();

        return success($list);
    }

    /**
     * 记录播放
     */
    public function play()
    {
        $id = input('id', 0, 'intval');
        
        $music = Music::find($id);
        if (!$music) {
            return error('音乐不存在', 404);
        }

        // 增加播放次数
        $music->incrementPlayCount();

        // 记录播放历史（如果已登录）
        $userId = $this->request->userId ?? 0;
        if ($userId > 0) {
            $history = new PlayHistory();
            $history->user_id = $userId;
            $history->music_id = $id;
            $history->save();
        }

        Log::info('音乐播放', ['music_id' => $id, 'user_id' => $userId]);

        return success(['play_count' => $music->play_count + 1]);
    }
}
