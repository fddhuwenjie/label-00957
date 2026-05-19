<?php
use think\facade\Route;

// 页面路由
Route::get('/', 'IndexController/index');
Route::get('/login', 'IndexController/login');

// 健康检查（公开，不走中间件）
Route::get('/api/health', 'api.HealthController/check');

// API路由
Route::group('api', function () {
    // 认证（无需登录）
    Route::get('/auth/public-key', 'api.AuthController/publicKey');
    Route::post('/auth/login', 'api.AuthController/login');

    // 需要登录的API
    Route::group('', function () {
        Route::post('/auth/logout', 'api.AuthController/logout');
        Route::get('/auth/info', 'api.AuthController/info');

        // 仪表盘
        Route::get('/dashboard/stats', 'api.DashboardController/stats');
        Route::get('/dashboard/recent-music', 'api.DashboardController/recentMusic');
        Route::get('/dashboard/top-music', 'api.DashboardController/topMusic');

        // 音乐管理
        Route::get('/music/list', 'api.MusicController/list');
        Route::get('/music/detail', 'api.MusicController/detail');
        Route::post('/music/create', 'api.MusicController/create');
        Route::post('/music/update/:id', 'api.MusicController/update');
        Route::delete('/music/delete/:id', 'api.MusicController/delete');
        Route::put('/music/status/:id', 'api.MusicController/updateStatus');
        Route::post('/music/upload-audio', 'api.MusicController/uploadAudio');
        Route::post('/music/upload-cover', 'api.MusicController/uploadCover');

        // 分类管理
        Route::get('/category/list', 'api.CategoryController/list');
        Route::post('/category/create', 'api.CategoryController/create');
        Route::put('/category/update/:id', 'api.CategoryController/update');
        Route::delete('/category/delete/:id', 'api.CategoryController/delete');

        // 用户管理
        Route::get('/user/list', 'api.UserController/list');
        Route::put('/user/status/:id', 'api.UserController/updateStatus');

        // 操作日志
        Route::get('/log/list', 'api.LogController/list');
    })->middleware('admin_auth');
});
