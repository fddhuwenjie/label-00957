/**
 * 认证页逻辑
 */
(function() {
    'use strict';

    // RSA 公钥缓存
    let publicKey = null;

    // 页面加载时预取公钥
    async function fetchPublicKey() {
        try {
            const res = await fetch('/api/auth/public-key');
            const data = await res.json();
            if (data.code === 200) {
                publicKey = data.data.public_key;
            }
        } catch (e) {
            console.warn('获取公钥失败', e);
        }
    }
    fetchPublicKey();

    // 使用 Web Crypto API 进行 RSA-OAEP 加密（与后台解密方式匹配）
    async function encryptPassword(password) {
        if (!publicKey) {
            await fetchPublicKey();
        }
        if (!publicKey) {
            throw new Error('公钥未加载，无法加密');
        }

        const pemHeader = '-----BEGIN PUBLIC KEY-----';
        const pemFooter = '-----END PUBLIC KEY-----';
        const pemContents = publicKey
            .replace(pemHeader, '')
            .replace(pemFooter, '')
            .replace(/\s/g, '');
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

    // 登录表单
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const btn = document.getElementById('loginBtn');
            const btnText = btn.querySelector('.btn-text');
            const btnLoading = btn.querySelector('.btn-loading');

            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            if (!email || !password) {
                Components.toast('请填写完整信息');
                return;
            }

            // 显示loading
            btn.disabled = true;
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline-flex';

            try {
                const encryptedPassword = await encryptPassword(password);
                const res = await Api.auth.login({ email, password: encryptedPassword });

                // 隐藏loading
                btn.disabled = false;
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';

                if (res.code === 200) {
                    Store.user.login(res.data.token, res.data.user);
                    Components.toast('登录成功');
                    setTimeout(() => {
                        window.location.href = '/';
                    }, 500);
                } else {
                    Components.toast(res.message || '登录失败', 4000);
                }
            } catch (err) {
                btn.disabled = false;
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
                Components.toast('登录失败，请重试', 4000);
            }
        });
    }

    // 注册表单
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const btn = document.getElementById('registerBtn');
            const btnText = btn.querySelector('.btn-text');
            const btnLoading = btn.querySelector('.btn-loading');

            const email = document.getElementById('email').value.trim();
            const nickname = document.getElementById('nickname').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (!email || !password) {
                Components.toast('请填写完整信息');
                return;
            }

            if (password.length < 6) {
                Components.toast('密码长度不能少于6位');
                return;
            }

            if (password !== confirmPassword) {
                Components.toast('两次密码输入不一致');
                return;
            }

            // 显示loading
            btn.disabled = true;
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline-flex';

            try {
                const encryptedPassword = await encryptPassword(password);
                const res = await Api.auth.register({ email, password: encryptedPassword, nickname });

                // 隐藏loading
                btn.disabled = false;
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';

                if (res.code === 200) {
                    Store.user.login(res.data.token, res.data.user);
                    Components.toast('注册成功');
                    setTimeout(() => {
                        window.location.href = '/';
                    }, 500);
                } else {
                    Components.toast(res.message || '注册失败', 4000);
                }
            } catch (err) {
                btn.disabled = false;
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
                Components.toast('注册失败，请重试', 4000);
            }
        });
    }

    // 密码显示/隐藏切换
    document.querySelectorAll('.password-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const iconShow = this.querySelector('.icon-show');
            const iconHide = this.querySelector('.icon-hide');
            
            if (input.type === 'password') {
                input.type = 'text';
                iconShow.style.display = 'none';
                iconHide.style.display = 'block';
            } else {
                input.type = 'password';
                iconShow.style.display = 'block';
                iconHide.style.display = 'none';
            }
        });
    });
})();
