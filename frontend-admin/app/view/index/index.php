<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>音乐发行平台 - 管理后台</title>
    <link rel="stylesheet" href="/assets/css/admin.css?v=2">
</head>
<body>
    <div id="app">
        <!-- 侧边栏 -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <span class="logo">🎵 MusicHub</span>
            </div>
            <nav class="sidebar-nav">
                <a href="#dashboard" class="nav-item active" data-page="dashboard">
                    <span class="nav-icon">📊</span>仪表盘
                </a>
                <a href="#music" class="nav-item" data-page="music">
                    <span class="nav-icon">🎵</span>音乐管理
                </a>
                <a href="#category" class="nav-item" data-page="category">
                    <span class="nav-icon">📁</span>分类管理
                </a>
                <a href="#user" class="nav-item" data-page="user">
                    <span class="nav-icon">👥</span>用户管理
                </a>
                <a href="#log" class="nav-item" data-page="log">
                    <span class="nav-icon">📝</span>操作日志
                </a>
            </nav>
        </aside>

        <!-- 主内容区 -->
        <main class="main">
            <header class="header">
                <button class="menu-toggle" id="menuToggle">☰</button>
                <div class="header-right">
                    <span class="admin-name" id="adminName">管理员</span>
                    <button class="btn-logout" id="logoutBtn">退出</button>
                </div>
            </header>
            <div class="content" id="content">
                <!-- 动态内容 -->
            </div>
        </main>
    </div>

    <!-- 模态框 -->
    <div class="modal" id="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">标题</h3>
                <button class="modal-close" id="modalClose">&times;</button>
            </div>
            <div class="modal-body" id="modalBody"></div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="modalCancel">取消</button>
                <button class="btn btn-primary" id="modalConfirm">确定</button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast" id="toast"></div>

    <script src="/assets/js/admin.js?v=2"></script>
</body>
</html>
