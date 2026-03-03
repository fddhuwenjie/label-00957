/**
 * 发现页逻辑
 */
(function() {
    'use strict';

    let currentPage = 1;
    let totalPages = 1;
    let currentCategory = 0;
    let isLoading = false;

    // 加载分类
    async function loadCategories() {
        const res = await Api.music.categories();
        if (res.code === 200 && res.data) {
            const tabs = document.getElementById('categoryTabs');
            if (tabs) {
                tabs.innerHTML = '<button class="filter-tab active" data-id="0">全部</button>' +
                    res.data.map(c => Components.renderCategoryTag(c)).join('');
            }
        }
    }

    // 加载音乐列表
    async function loadMusic(page) {
        if (isLoading) return;
        isLoading = true;
        currentPage = page || 1;

        Components.loading.show();
        const startTime = Date.now();
        const res = await Api.music.list({
            page: currentPage,
            limit: 20,
            category_id: currentCategory
        });
        const elapsed = Date.now() - startTime;
        if (elapsed < 300) {
            await new Promise(r => setTimeout(r, 300 - elapsed));
        }
        Components.loading.hide();
        isLoading = false;

        if (res.code === 200) {
            const list = document.getElementById('musicList');
            const data = res.data.data || res.data;
            const total = res.data.total || data.length;
            totalPages = Math.ceil(total / 20) || 1;

            list.innerHTML = '';
            if (data.length > 0) {
                list.innerHTML = data.map(m => Components.renderMusicListItem(m)).join('');
                Store.player.setPlaylist(data);
            } else {
                list.innerHTML = '<div class="empty-state"><svg viewBox="0 0 24 24"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg><p>暂无相关音乐</p></div>';
            }

            // 渲染分页
            Components.mountPagination('discoverPagination', currentPage, totalPages, loadMusic, total);

            // 跳回顶部
            if (page > 1) {
                window.scrollTo({ top: document.getElementById('musicList').offsetTop - 80, behavior: 'smooth' });
            }
        }
    }

    // 绑定事件
    function bindEvents() {
        // 分类切换
        document.getElementById('categoryTabs')?.addEventListener('click', (e) => {
            const tab = e.target.closest('.filter-tab');
            if (tab) {
                document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                currentCategory = parseInt(tab.dataset.id) || 0;
                loadMusic(1);
            }
        });

        // 排序切换
        document.querySelectorAll('.sort-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.sort-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                loadMusic(1);
            });
        });
    }

    // 初始化
    async function init() {
        const urlCategory = Utils.getQueryParam('category');
        if (urlCategory) {
            currentCategory = parseInt(urlCategory);
        }

        await loadCategories();
        await loadMusic(1);
        bindEvents();

        // 高亮当前分类
        if (currentCategory > 0) {
            setTimeout(() => {
                const tab = document.querySelector(`.filter-tab[data-id="${currentCategory}"]`);
                if (tab) {
                    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                }
            }, 100);
        }
    }

    init();
})();
