<?php
namespace app\controller\api;

use app\controller\BaseController;
use app\model\Music;
use app\model\User;
use app\model\Category;
use think\facade\Db;

class DashboardController extends BaseController
{
    public function stats()
    {
        $musicCount = Music::count();
        $userCount = User::count();
        $categoryCount = Category::where('status', 1)->count();
        $totalPlays = Music::sum('play_count');

        // 今日新增
        $today = date('Y-m-d');
        $todayMusic = Music::whereDay('created_at', $today)->count();
        $todayUsers = User::whereDay('created_at', $today)->count();

        return success([
            'music_count' => $musicCount,
            'user_count' => $userCount,
            'category_count' => $categoryCount,
            'total_plays' => $totalPlays,
            'today_music' => $todayMusic,
            'today_users' => $todayUsers
        ]);
    }

    public function recentMusic()
    {
        $list = Music::with(['category'])
            ->order('created_at', 'desc')
            ->limit(10)
            ->select();

        return success($list);
    }

    public function topMusic()
    {
        $list = Music::with(['category'])
            ->order('play_count', 'desc')
            ->limit(10)
            ->select();

        return success($list);
    }
}
