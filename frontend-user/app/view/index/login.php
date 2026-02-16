{extend name="layout/base" /}

{block name="content"}
<div class="page-auth">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>欢迎回来</h1>
                <p>登录您的账号，继续享受音乐</p>
            </div>
            <form class="auth-form" id="loginForm">
                <div class="form-group">
                    <label for="email">邮箱</label>
                    <input type="email" class="form-input" id="email" name="email" placeholder="请输入邮箱" required>
                </div>
                <div class="form-group">
                    <label for="password">密码</label>
                    <div class="password-input">
                        <input type="password" class="form-input" id="password" name="password" placeholder="请输入密码" required>
                        <button type="button" class="password-toggle" id="passwordToggle">
                            <svg class="icon-hide" viewBox="0 0 24 24" style="display:none"><path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/></svg>
                            <svg class="icon-show" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg" id="loginBtn">
                    <span class="btn-text">登录</span>
                    <span class="btn-loading" style="display:none;">
                        <svg class="spinner" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="31.4 31.4"/></svg>
                    </span>
                </button>
            </form>
            <div class="auth-footer">
                <p>还没有账号？<a href="/register">立即注册</a></p>
            </div>
        </div>
    </div>
</div>
{/block}

{block name="js"}
<script src="/asset/js?file=pages/auth"></script>
{/block}
