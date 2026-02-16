<!-- 移动端底部导航 -->
<nav class="tabbar mobile-only">
    <a href="/" class="tabbar-item {$pageId=='home'?'active':''}">
        <svg class="tabbar-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
        </svg>
        <span class="tabbar-text">首页</span>
    </a>
    <a href="/discover" class="tabbar-item {$pageId=='discover'?'active':''}">
        <svg class="tabbar-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 10.9c-.61 0-1.1.49-1.1 1.1s.49 1.1 1.1 1.1c.61 0 1.1-.49 1.1-1.1s-.49-1.1-1.1-1.1zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm2.19 12.19L6 18l3.81-8.19L18 6l-3.81 8.19z"/>
        </svg>
        <span class="tabbar-text">发现</span>
    </a>
    <a href="/ranking" class="tabbar-item {$pageId=='ranking'?'active':''}">
        <svg class="tabbar-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M7.5 21H2V9h5.5v12zm7.25-18h-5.5v18h5.5V3zM22 11h-5.5v10H22V11z"/>
        </svg>
        <span class="tabbar-text">排行</span>
    </a>
    <a href="/user" class="tabbar-item {$pageId=='user'?'active':''}">
        <svg class="tabbar-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
        </svg>
        <span class="tabbar-text">我的</span>
    </a>
</nav>
