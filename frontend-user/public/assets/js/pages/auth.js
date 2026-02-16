/**
 * 认证页逻辑
 */
(function() {
    'use strict';

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
                // 加密密码
                const encryptedPassword = await Utils.crypto.encrypt(password);
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
                    Components.toast(res.message || '登录失败');
                }
            } catch (err) {
                btn.disabled = false;
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
                Components.toast('登录失败，请重试');
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
                // 加密密码
                const encryptedPassword = await Utils.crypto.encrypt(password);
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
                    Components.toast(res.message || '注册失败');
                }
            } catch (err) {
                btn.disabled = false;
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
                Components.toast('注册失败，请重试');
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
