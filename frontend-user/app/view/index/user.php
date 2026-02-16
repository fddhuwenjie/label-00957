{extend name="layout/base" /}

{block name="content"}
<div class="page-user">
    <div class="container">
        <!-- 用户信息卡片 -->
        <div class="user-card">
            <div class="user-card-bg"></div>
            <div class="user-card-content">
                <img src="/assets/images/avatar-default.svg" alt="头像" class="user-card-avatar" id="userCardAvatar">
                <div class="user-card-info">
                    <h2 class="user-card-name" id="userCardName">未登录</h2>
                    <p class="user-card-email" id="userCardEmail">点击登录账号</p>
                </div>
                <a href="/login" class="btn btn-light btn-sm user-login-btn" id="userLoginBtn">登录</a>
            </div>
        </div>

        <!-- 功能菜单 -->
        <div class="user-menu">
            <a href="javascript:;" class="menu-item" data-tab="favorites">
                <svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                <span>我的收藏</span>
                <span class="menu-count" id="favoritesCount">0</span>
            </a>
            <a href="javascript:;" class="menu-item" data-tab="history">
                <svg viewBox="0 0 24 24"><path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/></svg>
                <span>播放历史</span>
                <span class="menu-count" id="historyCount">0</span>
            </a>
            <a href="javascript:;" class="menu-item" id="editProfile">
                <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                <span>编辑资料</span>
            </a>
            <a href="javascript:;" class="menu-item" id="logoutBtn" style="display:none;">
                <svg viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                <span>退出登录</span>
            </a>
        </div>

        <!-- 内容区域 -->
        <div class="user-content">
            <!-- 收藏列表 -->
            <div class="content-tab" id="tabFavorites">
                <div class="content-header">
                    <h3>我的收藏</h3>
                </div>
                <div class="music-list" id="favoritesList">
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                        <p>暂无收藏</p>
                    </div>
                </div>
            </div>

            <!-- 播放历史 -->
            <div class="content-tab" id="tabHistory" style="display:none;">
                <div class="content-header">
                    <h3>播放历史</h3>
                </div>
                <div class="music-list" id="historyList">
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24"><path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/></svg>
                        <p>暂无播放记录</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 编辑资料弹窗 -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>编辑资料</h3>
            <button class="modal-close" id="editModalClose">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>昵称</label>
                <input type="text" class="form-input" id="editNickname" placeholder="请输入昵称">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" id="editCancel">取消</button>
            <button class="btn btn-primary" id="editSave">保存</button>
        </div>
    </div>
</div>
{/block}

{block name="js"}
<script src="/asset/js?file=pages/user"></script>
{/block}
