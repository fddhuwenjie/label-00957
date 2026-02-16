/**
 * 搜索页逻辑
 */
(function() {
    'use strict';

    let currentPage = 1;
    let currentKeyword = '';
    let isLoading = false;
    let hasMore = true;

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
        loadMore: document.getElementById('loadMore'),
        loadMoreBtn: document.getElementById('loadMoreBtn')
    };

    // 执行搜索
    async function doSearch(keyword, reset = true) {
        if (!keyword.trim()) return;

        currentKeyword = keyword.trim();
        if (reset) {
            currentPage = 1;
            hasMore = true;
        }

        if (isLoading) return;
        isLoading = true;

        // 保存搜索历史
        Store.searchHistory.add(currentKeyword);
        renderHistory();

        // 显示结果区域
        elements.hotSearch.style.display = 'none';
        elements.searchHistory.style.display = 'none';
        elements.results.style.display = 'block';

        // 搜索按钮添加loading状态
        elements.searchBtn?.classList.add('loading');
        
        // 添加300ms loading延迟
        await new Promise(resolve => setTimeout(resolve, 300));
        
        const res = await Api.music.search({
            keyword: currentKeyword,
            page: currentPage,
            limit: 20
        });
        
        // 移除loading状态
        elements.searchBtn?.classList.remove('loading');
        isLoading = false;

        if (res.code === 200) {
            const data = res.data.data || res.data;
            const total = res.data.total || data.length;

            elements.resultsCount.textContent = total;

            if (reset) {
                elements.resultsList.innerHTML = '';
            }

            if (data.length > 0) {
                elements.resultsList.innerHTML += data.map(m => Components.renderMusicListItem(m)).join('');
                Store.player.setPlaylist(data);
            } else if (reset) {
                elements.resultsList.innerHTML = '<div class="empty-state"><p>未找到相关音乐</p></div>';
            }

            hasMore = data.length >= 20;
            elements.loadMore.style.display = hasMore ? 'block' : 'none';
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
        renderHistory();
    }

    // 绑定事件
    function bindEvents() {
        // 搜索按钮
        elements.searchBtn?.addEventListener('click', () => {
            doSearch(elements.input.value);
        });

        // 回车搜索
        elements.input?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                doSearch(elements.input.value);
            }
        });

        // 清空输入
        elements.clearBtn?.addEventListener('click', resetSearch);

        // 热门搜索标签
        document.getElementById('hotSearchTags')?.addEventListener('click', (e) => {
            const tag = e.target.closest('.hot-tag');
            if (tag) {
                elements.input.value = tag.textContent;
                doSearch(tag.textContent);
            }
        });

        // 历史搜索标签
        elements.historyTags?.addEventListener('click', (e) => {
            const tag = e.target.closest('.history-tag');
            if (tag) {
                elements.input.value = tag.textContent;
                doSearch(tag.textContent);
            }
        });

        // 清空历史
        elements.clearHistory?.addEventListener('click', () => {
            Store.searchHistory.clear();
            renderHistory();
        });

        // 加载更多
        elements.loadMoreBtn?.addEventListener('click', () => {
            currentPage++;
            doSearch(currentKeyword, false);
        });
    }

    // 初始化
    function init() {
        renderHistory();
        bindEvents();

        // 检查URL参数
        const keyword = Utils.getQueryParam('keyword');
        if (keyword) {
            elements.input.value = keyword;
            doSearch(keyword);
        }
    }

    init();
})();
