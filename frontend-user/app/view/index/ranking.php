{extend name="layout/base" /}

{block name="content"}
<div class="page-ranking">
    <div class="container">
        <!-- 榜单切换 -->
        <div class="ranking-tabs">
            <button class="ranking-tab active" data-type="hot">
                <svg viewBox="0 0 24 24"><path d="M13.5.67s.74 2.65.74 4.8c0 2.06-1.35 3.73-3.41 3.73-2.07 0-3.63-1.67-3.63-3.73l.03-.36C5.21 7.51 4 10.62 4 14c0 4.42 3.58 8 8 8s8-3.58 8-8C20 8.61 17.41 3.8 13.5.67zM11.71 19c-1.78 0-3.22-1.4-3.22-3.14 0-1.62 1.05-2.76 2.81-3.12 1.77-.36 3.6-1.21 4.62-2.58.39 1.29.59 2.65.59 4.04 0 2.65-2.15 4.8-4.8 4.8z"/></svg>
                热门榜
            </button>
            <button class="ranking-tab" data-type="new">
                <svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                新歌榜
            </button>
        </div>

        <!-- 榜单列表 -->
        <div class="ranking-content">
            <div class="ranking-header">
                <span class="ranking-col-rank">排名</span>
                <span class="ranking-col-info">歌曲信息</span>
                <span class="ranking-col-duration pc-only">时长</span>
                <span class="ranking-col-action">操作</span>
            </div>
            <div class="ranking-body" id="rankingBody">
                <!-- 动态加载 -->
            </div>
        </div>
    </div>
</div>
{/block}

{block name="js"}
<script src="/asset/js?file=pages/ranking"></script>
{/block}
