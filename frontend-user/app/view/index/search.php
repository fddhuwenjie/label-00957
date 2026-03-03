{extend name="layout/base" /}

{block name="css"}
<style>
#searchBtn.loading {
    position: relative;
    color: transparent !important;
    pointer-events: none;
}
#searchBtn.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    margin-top: -8px;
    margin-left: -8px;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: btn-spin 0.6s linear infinite;
}
@keyframes btn-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
{/block}

{block name="content"}
<div class="page-search">
    <div class="container">
        <!-- 搜索框 -->
        <div class="search-form">
            <div class="search-input-wrapper">
                <svg class="search-icon" viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                <input type="text" class="search-input-lg" placeholder="搜索音乐、歌手、专辑" id="searchInput" autofocus>
                <button class="search-clear" id="searchClear">&times;</button>
            </div>
            <button class="btn btn-primary" id="searchBtn">搜索</button>
        </div>

        <!-- 热门搜索 -->
        <div class="hot-search" id="hotSearch">
            <h3 class="hot-search-title">热门搜索</h3>
            <div class="hot-search-tags" id="hotSearchTags">
                <span class="hot-tag">周杰伦</span>
                <span class="hot-tag">邓紫棋</span>
                <span class="hot-tag">林俊杰</span>
                <span class="hot-tag">薛之谦</span>
                <span class="hot-tag">陈奕迅</span>
                <span class="hot-tag">Taylor Swift</span>
            </div>
        </div>

        <!-- 搜索历史 -->
        <div class="search-history" id="searchHistory">
            <div class="history-header">
                <h3 class="history-title">搜索历史</h3>
                <button class="history-clear" id="clearHistory">清空</button>
            </div>
            <div class="history-tags" id="historyTags">
                <!-- 动态加载 -->
            </div>
        </div>

        <!-- 搜索结果 -->
        <div class="search-results" id="searchResults" style="display:none;">
            <div class="results-header">
                <span class="results-count">找到 <strong id="resultsCount">0</strong> 首歌曲</span>
            </div>
            <div class="music-list" id="resultsList">
                <!-- 动态加载 -->
            </div>
            <!-- 分页 -->
            <div class="pagination-bar" id="searchPagination"></div>
        </div>
    </div>
</div>
{/block}

{block name="js"}
<script src="/asset/js?file=pages/search"></script>
<script>
// 确保loading效果生效
(function() {
    const searchBtn = document.getElementById('searchBtn');
    const searchInput = document.getElementById('searchInput');
    
    if (searchBtn && searchInput) {
        const originalClick = searchBtn.onclick;
        searchBtn.addEventListener('click', function() {
            if (searchInput.value.trim()) {
                searchBtn.classList.add('loading');
                setTimeout(() => {
                    searchBtn.classList.remove('loading');
                }, 800);
            }
        }, true);
    }
})();
</script>
{/block}
