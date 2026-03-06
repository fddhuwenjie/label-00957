<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{$title|default='音乐发行平台'}</title>
    <link rel="icon" href="/assets/images/favicon.ico">
    <!-- 动态加载CSS（通过PHP动态引入保护源码） -->
    <?php
    $cssVersion = '20260216v2';
    // 核心CSS：所有页面共用
    $cssFiles = ['variables', 'base', 'components', 'responsive'];
    foreach ($cssFiles as $file) {
        echo '<link rel="stylesheet" href="/asset/css?file=' . $file . '&v=' . $cssVersion . '">' . "\n    ";
    }
    // 页面CSS：根据当前 pageId 按需加载
    $currentPage = isset($pageId) ? $pageId : '';
    $pageCssMap = [
        'home'     => ['pages/home'],
        'auth'     => ['pages/auth'],
        'login'    => ['pages/auth'],
        'register' => ['pages/auth'],
        'user'     => ['pages/user'],
        'profile'  => ['pages/user'],
        'discover' => ['pages/discover'],
        'ranking'  => ['pages/ranking'],
        'search'   => ['pages/search'],
    ];
    if (isset($pageCssMap[$currentPage])) {
        foreach ($pageCssMap[$currentPage] as $file) {
            echo '<link rel="stylesheet" href="/asset/css?file=' . $file . '&v=' . $cssVersion . '">' . "\n    ";
        }
    }
    ?>
    {block name="css"}{/block}
</head>
<body class="device-{$device|default='pc'}" data-page="{$pageId|default=''}">
    <div id="app">
        <!-- 头部导航 -->
        {include file="layout/header" /}
        
        <!-- 主内容区 -->
        <main class="main-content">
            {block name="content"}{/block}
        </main>
        
        <!-- 底部导航（移动端） -->
        {include file="layout/tabbar" /}
        
        <!-- 播放器 -->
        {include file="components/player" /}
    </div>

    <!-- Toast提示 -->
    <div id="toast" class="toast"></div>
    
    <!-- Loading -->
    <div id="loading" class="loading-mask">
        <div class="loading-spinner"></div>
    </div>

    <!-- 动态加载JS（通过PHP动态引入保护源码） -->
    <?php
    // 动态加载核心JS（经过压缩混淆）
    $jsFiles = ['utils', 'api', 'store', 'components', 'app'];
    foreach ($jsFiles as $file) {
        echo '<script src="/asset/js?file=' . $file . '"></script>' . "\n    ";
    }
    ?>
    {block name="js"}{/block}
</body>
</html>
