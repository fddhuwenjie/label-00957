/**
 * 用户中心页逻辑
 */
(function() {
    'use strict';

    const elements = {
        avatar: document.getElementById('userCardAvatar'),
        name: document.getElementById('userCardName'),
        email: document.getElementById('userCardEmail'),
        loginBtn: document.getElementById('userLoginBtn'),
        logoutBtn: document.getElementById('logoutBtn'),
        favoritesCount: document.getElementById('favoritesCount'),
        historyCount: document.getElementById('historyCount'),
        favoritesList: document.getElementById('favoritesList'),
        historyList: document.getElementById('historyList'),
        tabFavorites: document.getElementById('tabFavorites'),
        tabHistory: document.getElementById('tabHistory'),
        editProfile: document.getElementById('editProfile'),
        editModal: document.getElementById('editModal'),
        editNickname: document.getElementById('editNickname'),
        editSave: document.getElementById('editSave'),
        editCancel: document.getElementById('editCancel'),
        editModalClose: document.getElementById('editModalClose')
    };

    let currentTab = 'favorites';

    // 更新用户信息显示
    function updateUserInfo() {
        if (Store.user.isLoggedIn && Store.user.data) {
            const user = Store.user.data;
            elements.avatar.src = user.avatar || '/assets/images/avatar-default.svg';
            elements.name.textContent = user.nickname || '用户';
            elements.email.textContent = user.email;
            elements.loginBtn.style.display = 'none';
            elements.logoutBtn.style.display = 'flex';
        } else {
            elements.name.textContent = '未登录';
            elements.email.textContent = '点击登录账号';
            elements.loginBtn.style.display = 'inline-flex';
            elements.logoutBtn.style.display = 'none';
        }
    }

    // 加载收藏列表
    async function loadFavorites() {
        if (!Store.user.isLoggedIn) {
            elements.favoritesList.innerHTML = '<div class="empty-state"><p>请先登录</p></div>';
            return;
        }

        Components.loading.show();
        const res = await Api.user.favorites({ page: 1, limit: 50 });
        Components.loading.hide();

        if (res.code === 200) {
            const data = res.data.data || res.data || [];
            elements.favoritesCount.textContent = data.length;

            if (data.length > 0) {
                // 更新收藏缓存
                if (window.FavoriteCache) {
                    data.forEach(f => {
                        const musicId = f.music_id || f.music?.id;
                        if (musicId) window.FavoriteCache.add(musicId);
                    });
                }
                
                elements.favoritesList.innerHTML = data.map(f => {
                    if (!f.music) return '';
                    return renderFavoriteItem(f.music);
                }).filter(Boolean).join('');
            } else {
                elements.favoritesList.innerHTML = '<div class="empty-state"><svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg><p>暂无收藏</p></div>';
            }
        } else {
            Components.toast(res.message || '加载失败');
        }
    }

    // 渲染收藏项（带取消收藏按钮）
    function renderFavoriteItem(music) {
        const defaultCover = '/assets/images/cover-default.svg';
        const coverUrl = music.cover_url || defaultCover;
        return `
            <div class="music-list-item" data-id="${music.id}">
                <img class="music-list-cover" src="${coverUrl}" alt="${music.title}" loading="lazy" onerror="this.onerror=null;this.src='${defaultCover}'">
                <div class="music-list-info">
                    <div class="music-list-title">${music.title}</div>
                    <div class="music-list-meta">${music.artist}${music.album ? ' · ' + music.album : ''}</div>
                </div>
                <span class="music-list-duration pc-only">${Utils.formatTime(music.duration)}</span>
                <div class="music-list-action">
                    <button class="btn-play" title="播放">
                        <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    </button>
                    <button class="btn-remove-favorite" title="取消收藏" data-music-id="${music.id}">
                        <svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                    </button>
                </div>
            </div>
        `;
    }

    // 加载播放历史
    async function loadHistory() {
        if (!Store.user.isLoggedIn) {
            elements.historyList.innerHTML = '<div class="empty-state"><p>请先登录</p></div>';
            return;
        }

        Components.loading.show();
        const res = await Api.user.history({ page: 1, limit: 50 });
        Components.loading.hide();

        if (res.code === 200) {
            const data = res.data.data || res.data || [];
            elements.historyCount.textContent = data.length;

            if (data.length > 0) {
                elements.historyList.innerHTML = data.map(h => {
                    if (!h.music) return '';
                    return Components.renderMusicListItem(h.music);
                }).filter(Boolean).join('');
            } else {
                elements.historyList.innerHTML = '<div class="empty-state"><svg viewBox="0 0 24 24"><path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/></svg><p>暂无播放记录</p></div>';
            }
        } else {
            Components.toast(res.message || '加载失败');
        }
    }

    // 切换Tab
    function switchTab(tab) {
        currentTab = tab;
        document.querySelectorAll('.menu-item[data-tab]').forEach(item => {
            item.classList.toggle('active', item.dataset.tab === tab);
        });
        
        if (tab === 'favorites') {
            elements.tabFavorites.style.display = 'block';
            elements.tabHistory.style.display = 'none';
            loadFavorites();
        } else {
            elements.tabFavorites.style.display = 'none';
            elements.tabHistory.style.display = 'block';
            loadHistory();
        }
    }

    // 取消收藏
    async function removeFavorite(musicId, itemEl) {
        const res = await Api.user.removeFavorite(musicId);
        if (res.code === 200) {
            // 更新缓存
            if (window.FavoriteCache) {
                window.FavoriteCache.remove(musicId);
            }
            // 移除DOM元素
            itemEl.remove();
            // 更新计数
            const count = parseInt(elements.favoritesCount.textContent) - 1;
            elements.favoritesCount.textContent = Math.max(0, count);
            
            // 如果列表为空，显示空状态
            if (count <= 0) {
                elements.favoritesList.innerHTML = '<div class="empty-state"><svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg><p>暂无收藏</p></div>';
            }
            
            Components.toast('已取消收藏');
        } else {
            Components.toast(res.message || '操作失败');
        }
    }

    // 显示编辑弹窗
    function showEditModal() {
        if (!Store.user.isLoggedIn) {
            Components.toast('请先登录');
            return;
        }
        elements.editNickname.value = Store.user.data?.nickname || '';
        elements.editModal.classList.add('show');
    }

    // 隐藏编辑弹窗
    function hideEditModal() {
        elements.editModal.classList.remove('show');
    }

    // 保存资料
    async function saveProfile() {
        const nickname = elements.editNickname.value.trim();
        if (!nickname) {
            Components.toast('请输入昵称');
            return;
        }

        Components.loading.show();
        const res = await Api.user.updateProfile({ nickname });
        Components.loading.hide();

        if (res.code === 200) {
            Store.user._data.nickname = nickname;
            Utils.storage.set('user', Store.user._data);
            updateUserInfo();
            Store.user.updateUI();
            hideEditModal();
            Components.toast('保存成功');
        } else {
            Components.toast(res.message || '保存失败');
        }
    }

    // 退出登录
    function logout() {
        Store.user.logout();
        // 重置收藏缓存
        if (window.FavoriteCache) {
            window.FavoriteCache.reset();
        }
        updateUserInfo();
        loadFavorites();
        Components.toast('已退出登录');
    }

    // 绑定事件
    function bindEvents() {
        // Tab切换
        document.querySelectorAll('.menu-item[data-tab]').forEach(item => {
            item.addEventListener('click', () => {
                switchTab(item.dataset.tab);
            });
        });

        // 编辑资料
        elements.editProfile?.addEventListener('click', showEditModal);
        elements.editModalClose?.addEventListener('click', hideEditModal);
        elements.editCancel?.addEventListener('click', hideEditModal);
        elements.editSave?.addEventListener('click', saveProfile);

        // 退出登录
        elements.logoutBtn?.addEventListener('click', logout);

        // 点击遮罩关闭
        elements.editModal?.addEventListener('click', (e) => {
            if (e.target === elements.editModal) {
                hideEditModal();
            }
        });

        // 取消收藏按钮点击
        document.addEventListener('click', async (e) => {
            const removeBtn = e.target.closest('.btn-remove-favorite');
            if (removeBtn) {
                e.preventDefault();
                e.stopPropagation();
                const musicId = removeBtn.dataset.musicId;
                const itemEl = removeBtn.closest('.music-list-item');
                if (musicId && itemEl) {
                    await removeFavorite(musicId, itemEl);
                }
            }
        });
    }

    // 初始化
    function init() {
        updateUserInfo();
        loadFavorites();
        bindEvents();
    }

    init();
})();
