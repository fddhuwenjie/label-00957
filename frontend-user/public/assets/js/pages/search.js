/**
 * 搜索页逻辑
 */
(function() {
    'use strict';

    let currentPage = 1;
    let totalPages = 1;
    let currentKeyword = '';
    let isLoading = false;

    const elements = {
        input: document.getElementById('searchInput'),
        clearBtn: document.getElementById('searchClear'),
        searchBtn: document.getElementById('searchBtn'),
        hotSearch: document.getElementById('hotSearch'),
        searchHistory: document.getElementById('searchHistory'),
        historyTags: document.getElementById('historyTags'),
        clearHistory: document.getElementById('clearHistory'),
        results: document.getElementById('searchResults'),
        resultsList: document.getElementById('resultsList'),
        resultsCount: document.getElementById('resultsCount'),
        pagination: document.getElementById('searchPagination')
    };

    // 执行搜索
    async function doSearch(keyword, page) {
        if (!keyword.trim()) return;

        currentKeyword = keyword.trim();
        currentPage = page || 1;

        if (isLoading) return;
        isLoading = true;

        // 保存搜索历史
        Store.searchHistory.add(currentKeyword);
        renderHistory();

        // 显示结果区域
        elements.hotSearch.style.display = 'none';
        elements.searchHistory.style.display = 'none';
        elements.results.style.display = 'block';

        // 搜索按钮 loading
        elements.searchBtn?.classList.add('loading');
        await new Promise(resolve => setTimeout(resolve, 300));

        const res = await Api.music.search({
            keyword: currentKeyword,
            page: currentPage,
            limit: 20
        });

        elements.searchBtn?.classList.remove('loading');
        isLoading = false;

        if (res.code === 200) {
            const data = res.data.data || res.data;
            const total = res.data.total || data.length;
            totalPages = Math.ceil(total / 20) || 1;

            elements.resultsCount.textContent = total;
            elements.resultsList.innerHTML = '';

            if (data.length > 0) {
                elements.resultsList.innerHTML = data.map(m => Components.renderMusicListItem(m)).join('');
                Store.player.setPlaylist(data);
            } else {
                elements.resultsList.innerHTML = '<div class="empty-state"><p>未找到相关音乐</p></div>';
            }

            // 渲染分页
            Components.mountPagination('searchPagination', currentPage, totalPages, (p) => doSearch(currentKeyword, p), total);

            // 翻页时滚动到结果顶部
            if (page > 1) {
                window.scrollTo({ top: elements.results.offsetTop - 80, behavior: 'smooth' });
            }
        }
    }

    // 渲染搜索历史
    function renderHistory() {
        const list = Store.searchHistory.list;
        if (list.length > 0) {
            elements.historyTags.innerHTML = list.map(k =>
                `<span class="history-tag">${k}</span>`
            ).join('');
            elements.searchHistory.style.display = 'block';
        } else {
            elements.searchHistory.style.display = 'none';
        }
    }

    // 重置搜索
    function resetSearch() {
        elements.input.value = '';
        elements.results.style.display = 'none';
        elements.hotSearch.style.display = 'block';
        if (elements.pagination) elements.pagination.innerHTML = '';
        renderHistory();
    }

    // 绑定事件
    function bindEvents() {
        elements.searchBtn?.addEventListener('click', () => {
            doSearch(elements.input.value, 1);
        });

        elements.input?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') doSearch(elements.input.value, 1);
        });

        elements.clearBtn?.addEventListener('click', resetSearch);

        document.getElementById('hotSearchTags')?.addEventListener('click', (e) => {
            const tag = e.target.closest('.hot-tag');
            if (tag) {
                elements.input.value = tag.textContent;
                doSearch(tag.textContent, 1);
            }
        });

        elements.historyTags?.addEventListener('click', (e) => {
            const tag = e.target.closest('.history-tag');
            if (tag) {
                elements.input.value = tag.textContent;
                doSearch(tag.textContent, 1);
            }
        });

        elements.clearHistory?.addEventListener('click', () => {
            Store.searchHistory.clear();
            renderHistory();
        });
    }

    // 初始化
    function init() {
        renderHistory();
        bindEvents();

        const keyword = Utils.getQueryParam('keyword');
        if (keyword) {
            elements.input.value = keyword;
            doSearch(keyword, 1);
        }
    }

    init();
})();
