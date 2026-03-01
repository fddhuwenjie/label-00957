<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - 音乐发行平台管理后台</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "PingFang SC", "Microsoft YaHei", sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 16px;
            padding: 48px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-header h1 {
            font-size: 24px;
            color: #1f2937;
            margin-bottom: 8px;
        }
        .login-header p {
            color: #6b7280;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
        }
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        .btn {
            width: 100%;
            padding: 14px;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #4f46e5;
        }
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .error-msg {
            color: #ef4444;
            font-size: 14px;
            margin-top: 16px;
            text-align: center;
            display: none;
        }
        .password-input {
            position: relative;
        }
        .password-input .form-input {
            padding-right: 44px;
        }
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            padding: 4px;
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            transition: color 0.2s;
        }
        .password-toggle:hover {
            color: #6366f1;
        }
        .password-toggle svg {
            width: 20px;
            height: 20px;
            fill: currentColor;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h1>🎵 音乐发行平台</h1>
            <p>管理后台登录</p>
        </div>
        <form id="loginForm">
            <div class="form-group">
                <label for="username">用户名</label>
                <input type="text" class="form-input" id="username" name="username" placeholder="请输入用户名" required>
            </div>
            <div class="form-group">
                <label for="password">密码</label>
                <div class="password-input">
                    <input type="password" class="form-input" id="password" name="password" placeholder="请输入密码" required>
                    <button type="button" class="password-toggle" id="passwordToggle">
                        <svg class="icon-hide" viewBox="0 0 24 24"><path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/></svg>
                        <svg class="icon-show" viewBox="0 0 24 24" style="display:none"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn" id="loginBtn">登录</button>
            <p class="error-msg" id="errorMsg"></p>
        </form>
    </div>

    <script>
        let publicKey = null;
        
        // 页面加载时获取公钥
        async function fetchPublicKey() {
            try {
                const res = await fetch('/api/auth/public-key');
                const data = await res.json();
                if (data.code === 200) {
                    publicKey = data.data.public_key;
                }
            } catch (e) {
                console.error('获取公钥失败', e);
            }
        }
        fetchPublicKey();

        // 使用原生 Web Crypto API 进行 RSA 加密
        async function encryptPassword(password) {
            if (!publicKey) {
                throw new Error('公钥未加载');
            }
            const pemHeader = '-----BEGIN PUBLIC KEY-----';
            const pemFooter = '-----END PUBLIC KEY-----';
            const pemContents = publicKey.replace(pemHeader, '').replace(pemFooter, '').replace(/\s/g, '');
            const binaryDer = Uint8Array.from(atob(pemContents), c => c.charCodeAt(0));

            const cryptoKey = await window.crypto.subtle.importKey(
                'spki',
                binaryDer.buffer,
                { name: 'RSA-OAEP', hash: 'SHA-1' },
                false,
                ['encrypt']
            );

            const encoded = new TextEncoder().encode(password);
            const encrypted = await window.crypto.subtle.encrypt(
                { name: 'RSA-OAEP' },
                cryptoKey,
                encoded
            );

            return btoa(String.fromCharCode(...new Uint8Array(encrypted)));
        }

        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const btn = document.getElementById('loginBtn');
            const errorMsg = document.getElementById('errorMsg');
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            btn.disabled = true;
            btn.textContent = '登录中...';
            errorMsg.style.display = 'none';

            try {
                // 加密密码
                if (!publicKey) {
                    await fetchPublicKey();
                }
                const encryptedPassword = await encryptPassword(password);
                if (!encryptedPassword) {
                    throw new Error('密码加密失败');
                }

                const res = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password: encryptedPassword })
                });
                const data = await res.json();

                if (data.code === 200) {
                    localStorage.setItem('admin_token', data.data.token);
                    localStorage.setItem('admin_info', JSON.stringify(data.data.admin));
                    window.location.href = '/';
                } else {
                    errorMsg.textContent = data.message || '登录失败';
                    errorMsg.style.display = 'block';
                }
            } catch (err) {
                errorMsg.textContent = err.message || '网络错误，请稍后重试';
                errorMsg.style.display = 'block';
            }

            btn.disabled = false;
            btn.textContent = '登录';
        });

        // 检查是否已登录
        if (localStorage.getItem('admin_token')) {
            window.location.href = '/';
        }

        // 密码显示/隐藏切换
        document.getElementById('passwordToggle').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const iconShow = this.querySelector('.icon-show');
            const iconHide = this.querySelector('.icon-hide');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                iconHide.style.display = 'none';
                iconShow.style.display = 'block';
            } else {
                passwordInput.type = 'password';
                iconHide.style.display = 'block';
                iconShow.style.display = 'none';
            }
        });
    </script>
</body>
</html>
