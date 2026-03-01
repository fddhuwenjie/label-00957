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
            <a href="/" class="nav-item {$pageId=='home'?'active':''}">首页</a>
            <a href="/discover" class="nav-item {$pageId=='discover'?'active':''}">发现</a>
            <a href="/ranking" class="nav-item {$pageId=='ranking'?'active':''}">排行榜</a>
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
                <div class="user-info-text pc-only">
                    <span class="user-name" id="userName"></span>
                    <span class="user-email" id="userEmail"></span>
                </div>
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
