<?php /*a:5:{s:38:"/var/www/html/app/view/index/index.php";i:1771057664;s:38:"/var/www/html/app/view/layout/base.php";i:1771172592;s:40:"/var/www/html/app/view/layout/header.php";i:1771172667;s:40:"/var/www/html/app/view/layout/tabbar.php";i:1771172018;s:44:"/var/www/html/app/view/components/player.php";i:1771172130;}*/ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo htmlentities((string) (isset($title) && ($title !== '')?$title:'音乐发行平台')); ?></title>
    <link rel="icon" href="/assets/images/favicon.ico">
    <!-- 动态加载CSS（通过PHP动态引入保护源码） -->
    <?php
    // 动态加载核心CSS
    $cssFiles = ['variables', 'base', 'components', 'responsive'];
    $pageCss = ['home', 'auth', 'user', 'discover', 'ranking', 'search'];
    $cssVersion = '20260216v2';
    foreach ($cssFiles as $file) {
        echo '<link rel="stylesheet" href="/asset/css?file=' . $file . '&v=' . $cssVersion . '">' . "\n    ";
    }
    foreach ($pageCss as $file) {
        echo '<link rel="stylesheet" href="/asset/css?file=pages/' . $file . '&v=' . $cssVersion . '">' . "\n    ";
    }
    ?>
    
</head>
<body class="device-<?php echo htmlentities((string) (isset($device) && ($device !== '')?$device:'pc')); ?>" data-page="<?php echo htmlentities((string) (isset($pageId) && ($pageId !== '')?$pageId:'')); ?>">
    <div id="app">
        <!-- 头部导航 -->
        <header class="header">
    <div class="header-inner container">
        <!-- Logo -->
        <a href="/" class="logo">
            <svg class="logo-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/>
            </svg>
            <span class="logo-text">MusicHub</span>
        </a>

        <!-- PC端导航 -->
        <nav class="nav-menu pc-only">
            <a href="/" class="nav-item <?php echo $pageId=='home' ? 'active' : ''; ?>">首页</a>
            <a href="/discover" class="nav-item <?php echo $pageId=='discover' ? 'active' : ''; ?>">发现</a>
            <a href="/ranking" class="nav-item <?php echo $pageId=='ranking' ? 'active' : ''; ?>">排行榜</a>
        </nav>

        <!-- 搜索框 -->
        <div class="search-box">
            <svg class="search-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
            </svg>
            <input type="text" class="search-input" placeholder="搜索音乐、歌手" id="headerSearch">
        </div>

        <!-- 用户区域 -->
        <div class="user-area">
            <a href="/user" class="user-logged">
                <img src="/assets/images/avatar-default.svg" alt="头像" class="user-avatar" id="userAvatar">
                <span class="user-name pc-only" id="userName"></span>
            </a>
            <div class="user-guest">
                <a href="/login" class="btn btn-text">登录</a>
                <a href="/register" class="btn btn-primary btn-sm">注册</a>
            </div>
        </div>

        <!-- 移动端菜单按钮 -->
        <button class="menu-toggle mobile-only" id="menuToggle">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>

<!-- 移动端侧边菜单 -->
<div class="mobile-menu" id="mobileMenu">
    <div class="mobile-menu-header">
        <a href="/user" class="mobile-user-info" id="mobileUserInfo">
            <img src="/assets/images/avatar-default.svg" alt="头像" class="mobile-avatar">
            <span class="mobile-user-name">点击登录</span>
        </a>
    </div>
    <nav class="mobile-nav">
        <a href="/" class="mobile-nav-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
            <span>首页</span>
        </a>
        <a href="/discover" class="mobile-nav-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 10.9c-.61 0-1.1.49-1.1 1.1s.49 1.1 1.1 1.1c.61 0 1.1-.49 1.1-1.1s-.49-1.1-1.1-1.1zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm2.19 12.19L6 18l3.81-8.19L18 6l-3.81 8.19z"/></svg>
            <span>发现</span>
        </a>
        <a href="/ranking" class="mobile-nav-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M7.5 21H2V9h5.5v12zm7.25-18h-5.5v18h5.5V3zM22 11h-5.5v10H22V11z"/></svg>
            <span>排行榜</span>
        </a>
    </nav>
</div>
<div class="mobile-menu-overlay" id="menuOverlay"></div>

        
        <!-- 主内容区 -->
        <main class="main-content">
            
<div class="page-home">
    <!-- 轮播图 -->
    <section class="banner-section">
        <div class="container">
            <div class="banner-swiper" id="bannerSwiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <div class="banner-item" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <div class="banner-content">
                                <h2>发现好音乐</h2>
                                <p>海量正版音乐，随心畅听</p>
                                <a href="/discover" class="btn btn-light">立即探索</a>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="banner-item" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <div class="banner-content">
                                <h2>热门排行榜</h2>
                                <p>实时更新，把握音乐潮流</p>
                                <a href="/ranking" class="btn btn-light">查看榜单</a>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="banner-item" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <div class="banner-content">
                                <h2>音乐发行</h2>
                                <p>让你的音乐被更多人听到</p>
                                <a href="/register" class="btn btn-light">开始发行</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </section>

    <!-- 分类导航 -->
    <section class="category-section">
        <div class="container">
            <div class="category-grid" id="categoryGrid">
                <!-- 动态加载 -->
            </div>
        </div>
    </section>

    <!-- 推荐音乐 -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">推荐音乐</h2>
                <a href="/discover" class="section-more">更多 &gt;</a>
            </div>
            <div class="music-grid" id="recommendGrid">
                <!-- 动态加载 -->
            </div>
        </div>
    </section>

    <!-- 热门排行 -->
    <section class="section section-alt">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">热门排行</h2>
                <a href="/ranking" class="section-more">更多 &gt;</a>
            </div>
            <div class="ranking-list" id="rankingList">
                <!-- 动态加载 -->
            </div>
        </div>
    </section>

    <!-- 最新发行 -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">最新发行</h2>
                <a href="/discover?sort=new" class="section-more">更多 &gt;</a>
            </div>
            <div class="music-grid" id="newReleaseGrid">
                <!-- 动态加载 -->
            </div>
        </div>
    </section>
</div>

        </main>
        
        <!-- 底部导航（移动端） -->
        <!-- 移动端底部导航 -->
<nav class="tabbar mobile-only">
    <a href="/" class="tabbar-item <?php echo $pageId=='home' ? 'active' : ''; ?>">
        <svg class="tabbar-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
        </svg>
        <span class="tabbar-text">首页</span>
    </a>
    <a href="/discover" class="tabbar-item <?php echo $pageId=='discover' ? 'active' : ''; ?>">
        <svg class="tabbar-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 10.9c-.61 0-1.1.49-1.1 1.1s.49 1.1 1.1 1.1c.61 0 1.1-.49 1.1-1.1s-.49-1.1-1.1-1.1zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm2.19 12.19L6 18l3.81-8.19L18 6l-3.81 8.19z"/>
        </svg>
        <span class="tabbar-text">发现</span>
    </a>
    <a href="/ranking" class="tabbar-item <?php echo $pageId=='ranking' ? 'active' : ''; ?>">
        <svg class="tabbar-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M7.5 21H2V9h5.5v12zm7.25-18h-5.5v18h5.5V3zM22 11h-5.5v10H22V11z"/>
        </svg>
        <span class="tabbar-text">排行</span>
    </a>
    <a href="/user" class="tabbar-item <?php echo $pageId=='user' ? 'active' : ''; ?>">
        <svg class="tabbar-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
        </svg>
        <span class="tabbar-text">我的</span>
    </a>
</nav>

        
        <!-- 播放器 -->
        <!-- 全局播放器 -->
<div class="player" id="player">
    <div class="player-inner container">
        <!-- 歌曲信息 -->
        <div class="player-info">
            <img src="/assets/images/cover-default.svg" alt="封面" class="player-cover" id="playerCover">
            <div class="player-meta">
                <div class="player-title" id="playerTitle">未播放</div>
                <div class="player-artist" id="playerArtist">-</div>
            </div>
        </div>

        <!-- 进度条 -->
        <div class="player-progress">
            <span class="player-time" id="playerCurrentTime">0:00</span>
            <div class="progress-bar" id="progressBar">
                <div class="progress-played" id="progressPlayed"></div>
                <div class="progress-handle" id="progressHandle"></div>
            </div>
            <span class="player-time" id="playerDuration">0:00</span>
        </div>

        <!-- 控制按钮 -->
        <div class="player-controls">
            <button class="player-btn" id="btnPrev" title="上一首">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 6h2v12H6zm3.5 6l8.5 6V6z"/></svg>
            </button>
            <button class="player-btn player-btn-play" id="btnPlay" title="播放">
                <svg class="icon-play" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                <svg class="icon-pause" viewBox="0 0 24 24" fill="currentColor" style="display:none"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
            </button>
            <button class="player-btn" id="btnNext" title="下一首">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z"/></svg>
            </button>
        </div>

        <!-- 附加控制 -->
        <div class="player-extra pc-only">
            <button class="player-btn" id="btnFavorite" title="收藏">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16.5 3c-1.74 0-3.41.81-4.5 2.09C10.91 3.81 9.24 3 7.5 3 4.42 3 2 5.42 2 8.5c0 3.78 3.4 6.86 8.55 11.54L12 21.35l1.45-1.32C18.6 15.36 22 12.28 22 8.5 22 5.42 19.58 3 16.5 3zm-4.4 15.55l-.1.1-.1-.1C7.14 14.24 4 11.39 4 8.5 4 6.5 5.5 5 7.5 5c1.54 0 3.04.99 3.57 2.36h1.87C13.46 5.99 14.96 5 16.5 5c2 0 3.5 1.5 3.5 3.5 0 2.89-3.14 5.74-7.9 10.05z"/></svg>
            </button>
            <div class="volume-control">
                <button class="player-btn" id="btnVolume" title="音量">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/></svg>
                </button>
                <div class="volume-slider">
                    <input type="range" min="0" max="100" value="80" id="volumeSlider">
                </div>
            </div>
            <button class="player-btn" id="btnPlaylist" title="播放列表">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M15 6H3v2h12V6zm0 4H3v2h12v-2zM3 16h8v-2H3v2zM17 6v8.18c-.31-.11-.65-.18-1-.18-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3V8h3V6h-5z"/></svg>
            </button>
        </div>
    </div>

    <!-- 音频元素 -->
    <audio id="audioElement" preload="metadata"></audio>
</div>

<!-- 播放列表弹窗 -->
<div class="playlist-modal" id="playlistModal">
    <div class="playlist-header">
        <h3>播放列表</h3>
        <button class="playlist-close" id="playlistClose">&times;</button>
    </div>
    <div class="playlist-content" id="playlistContent">
        <div class="playlist-empty">暂无播放列表</div>
    </div>
</div>

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
    
<script src="/asset/js?file=pages/home"></script>

</body>
</html>
