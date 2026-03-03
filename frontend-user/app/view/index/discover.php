{extend name="layout/base" /}

{block name="content"}
<div class="page-discover">
    <div class="container">
        <!-- 分类筛选 -->
        <div class="filter-bar">
            <div class="filter-tabs" id="categoryTabs">
                <button class="filter-tab active" data-id="0">全部</button>
                <!-- 动态加载分类 -->
            </div>
        </div>

        <!-- 排序 -->
        <div class="sort-bar">
            <span class="sort-label">排序：</span>
            <button class="sort-btn active" data-sort="new">最新</button>
            <button class="sort-btn" data-sort="hot">最热</button>
        </div>

        <!-- 音乐列表 -->
        <div class="music-list" id="musicList">
            <!-- 动态加载 -->
        </div>

        <!-- 分页 -->
        <div class="pagination-bar" id="discoverPagination"></div>
    </div>
</div>
{/block}

{block name="js"}
<script src="/asset/js?file=pages/discover"></script>
{/block}
