{extend name="layout/base" /}

{block name="content"}
<div class="page-home">
    <!-- 轮播图 -->
    <section class="banner-section">
        <div class="container">
            <div class="banner-swiper" id="bannerSwiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <div class="banner-item" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <div class="banner-content">
                                <h2>发现好音乐</h2>
                                <p>海量正版音乐，随心畅听</p>
                                <a href="/discover" class="btn btn-light">立即探索</a>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="banner-item" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <div class="banner-content">
                                <h2>热门排行榜</h2>
                                <p>实时更新，把握音乐潮流</p>
                                <a href="/ranking" class="btn btn-light">查看榜单</a>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="banner-item" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <div class="banner-content">
                                <h2>音乐发行</h2>
                                <p>让你的音乐被更多人听到</p>
                                <a href="/register" class="btn btn-light">开始发行</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </section>

    <!-- 分类导航 -->
    <section class="category-section">
        <div class="container">
            <div class="category-grid" id="categoryGrid">
                <!-- 动态加载 -->
            </div>
        </div>
    </section>

    <!-- 推荐音乐 -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">推荐音乐</h2>
                <a href="/discover" class="section-more">更多 &gt;</a>
            </div>
            <div class="music-grid" id="recommendGrid">
                <!-- 动态加载 -->
            </div>
        </div>
    </section>

    <!-- 热门排行 -->
    <section class="section section-alt">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">热门排行</h2>
                <a href="/ranking" class="section-more">更多 &gt;</a>
            </div>
            <div class="ranking-list" id="rankingList">
                <!-- 动态加载 -->
            </div>
        </div>
    </section>

    <!-- 最新发行 -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">最新发行</h2>
                <a href="/discover?sort=new" class="section-more">更多 &gt;</a>
            </div>
            <div class="music-grid" id="newReleaseGrid">
                <!-- 动态加载 -->
            </div>
        </div>
    </section>
</div>
{/block}

{block name="js"}
<script src="/asset/js?file=pages/home"></script>
{/block}
