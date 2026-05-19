<?php
/**
 * 路由配置
 */
use think\facade\Route;

// 页面路由
Route::get('/', 'IndexController/index');
Route::get('/discover', 'IndexController/discover');
Route::get('/ranking', 'IndexController/ranking');
Route::get('/play', 'IndexController/play');
Route::get('/search', 'IndexController/search');
Route::get('/user', 'IndexController/user');
Route::get('/login', 'IndexController/login');
Route::get('/register', 'IndexController/register');
Route::get('/dashboard', 'IndexController/index'); // 兼容重定向

// 动态资源路由（保护源码）
Route::get('/asset/css', 'api.AssetController/css');
Route::get('/asset/js', 'api.AssetController/js');

// 健康检查（公开，不走中间件）
Route::get('/api/health', 'api.HealthController/check');

// API路由
Route::group('api', function () {
    // 认证
    Route::get('/auth/public-key', 'api.AuthController/publicKey');
    Route::post('/auth/register', 'api.AuthController/register');
    Route::post('/auth/login', 'api.AuthController/login');
    Route::post('/auth/logout', 'api.AuthController/logout')->middleware('auth');
    
    // 兼容管理端接口（返回空数据）
    Route::get('/dashboard/stats', function() {
        return json(['code' => 200, 'data' => []]);
    });
    
    // 音乐（公开）
    Route::get('/music/list', 'api.MusicController/list');
    Route::get('/music/detail', 'api.MusicController/detail');
    Route::get('/music/search', 'api.MusicController/search');
    Route::get('/music/recommend', 'api.MusicController/recommend');
    Route::get('/music/ranking', 'api.MusicController/ranking');
    Route::get('/music/categories', 'api.MusicController/categories');
    Route::post('/music/play', 'api.MusicController/play');
    
    // 用户（需认证）
    Route::group('user', function () {
        Route::get('/profile', 'api.UserController/profile');
        Route::put('/profile', 'api.UserController/updateProfile');
        Route::get('/favorites', 'api.UserController/favorites');
        Route::post('/favorite/add', 'api.UserController/addFavorite');
        Route::delete('/favorite/:id', 'api.UserController/removeFavorite');
        Route::get('/history', 'api.UserController/history');
    })->middleware('auth');
});
