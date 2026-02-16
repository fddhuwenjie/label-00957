/**
 * 发现页逻辑
 */
(function() {
    'use strict';

    let currentPage = 1;
    let currentCategory = 0;
    let currentSort = 'new';
    let isLoading = false;
    let hasMore = true;

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
    async function loadMusic(reset = false) {
        if (isLoading) return;
        isLoading = true;

        if (reset) {
            currentPage = 1;
            hasMore = true;
        }

        Components.loading.show();
        const res = await Api.music.list({
            page: currentPage,
            limit: 20,
            category_id: currentCategory
        });
        Components.loading.hide();
        isLoading = false;

        if (res.code === 200) {
            const list = document.getElementById('musicList');
            const data = res.data.data || res.data;
            
            if (reset) {
                list.innerHTML = '';
            }

            if (data.length > 0) {
                list.innerHTML += data.map(m => Components.renderMusicListItem(m)).join('');
                Store.player.setPlaylist(data);
            }

            hasMore = data.length >= 20;
            updateLoadMore();
        }
    }

    // 更新加载更多按钮
    function updateLoadMore() {
        const loadMore = document.getElementById('loadMore');
        const btn = document.getElementById('loadMoreBtn');
        if (loadMore && btn) {
            loadMore.style.display = hasMore ? 'block' : 'none';
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
                loadMusic(true);
            }
        });

        // 排序切换
        document.querySelectorAll('.sort-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.sort-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentSort = btn.dataset.sort;
                loadMusic(true);
            });
        });

        // 加载更多
        document.getElementById('loadMoreBtn')?.addEventListener('click', () => {
            currentPage++;
            loadMusic();
        });
    }

    // 初始化
    async function init() {
        // 检查URL参数
        const urlCategory = Utils.getQueryParam('category');
        if (urlCategory) {
            currentCategory = parseInt(urlCategory);
        }

        await loadCategories();
        await loadMusic(true);
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
