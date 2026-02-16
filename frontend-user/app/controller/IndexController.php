<?php
namespace app\controller;

use think\facade\View;

/**
 * 首页控制器
 * 多端合一响应式入口
 */
class IndexController extends BaseController
{
    /**
     * 首页
     */
    public function index()
    {
        $device = detectDevice();
        
        View::assign([
            'device' => $device,
            'title' => '音乐发行平台 - 发现好音乐',
            'pageId' => 'home'
        ]);
        
        return View::fetch('index/index');
    }

    /**
     * 发现页
     */
    public function discover()
    {
        View::assign([
            'title' => '发现音乐',
            'pageId' => 'discover'
        ]);
        return View::fetch('index/discover');
    }

    /**
     * 排行榜
     */
    public function ranking()
    {
        View::assign([
            'title' => '排行榜',
            'pageId' => 'ranking'
        ]);
        return View::fetch('index/ranking');
    }

    /**
     * 播放页
     */
    public function play()
    {
        $id = input('id', 0, 'intval');
        View::assign([
            'musicId' => $id,
            'title' => '正在播放',
            'pageId' => 'play'
        ]);
        return View::fetch('index/play');
    }

    /**
     * 搜索页
     */
    public function search()
    {
        View::assign([
            'title' => '搜索',
            'pageId' => 'search'
        ]);
        return View::fetch('index/search');
    }

    /**
     * 用户中心
     */
    public function user()
    {
        View::assign([
            'title' => '我的',
            'pageId' => 'user'
        ]);
        return View::fetch('index/user');
    }

    /**
     * 登录页
     */
    public function login()
    {
        View::assign([
            'title' => '登录',
            'pageId' => 'login'
        ]);
        return View::fetch('index/login');
    }

    /**
     * 注册页
     */
    public function register()
    {
        View::assign([
            'title' => '注册',
            'pageId' => 'register'
        ]);
        return View::fetch('index/register');
    }
}
