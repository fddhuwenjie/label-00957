/**
 * 首页逻辑
 */
(function() {
    'use strict';

    // 加载分类
    async function loadCategories() {
        const res = await Api.music.categories();
        if (res.code === 200 && res.data) {
            const grid = document.getElementById('categoryGrid');
            if (grid) {
                grid.innerHTML = res.data.map(c => Components.renderCategoryGridItem(c)).join('');
            }
        }
    }

    // 加载推荐音乐
    async function loadRecommend() {
        const res = await Api.music.recommend(12);
        if (res.code === 200 && res.data) {
            const grid = document.getElementById('recommendGrid');
            if (grid) {
                grid.innerHTML = res.data.map(m => Components.renderMusicCard(m)).join('');
                Store.player.setPlaylist(res.data);
            }
        }
    }

    // 加载排行榜
    async function loadRanking() {
        const res = await Api.music.ranking('hot', 10);
        if (res.code === 200 && res.data) {
            const list = document.getElementById('rankingList');
            if (list) {
                list.innerHTML = `<div class="music-list">${res.data.map((m, i) => Components.renderMusicListItem(m, i)).join('')}</div>`;
            }
        }
    }

    // 加载最新发行
    async function loadNewRelease() {
        const res = await Api.music.ranking('new', 12);
        if (res.code === 200 && res.data) {
            const grid = document.getElementById('newReleaseGrid');
            if (grid) {
                grid.innerHTML = res.data.map(m => Components.renderMusicCard(m)).join('');
            }
        }
    }

    // 轮播图
    function initBanner() {
        const swiper = document.getElementById('bannerSwiper');
        if (!swiper) return;

        let currentIndex = 0;
        const slides = swiper.querySelectorAll('.swiper-slide');
        const total = slides.length;

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.style.transform = `translateX(${(i - index) * 100}%)`;
            });
        }

        function nextSlide() {
            currentIndex = (currentIndex + 1) % total;
            showSlide(currentIndex);
        }

        showSlide(0);
        setInterval(nextSlide, 5000);
    }

    // 初始化
    async function init() {
        initBanner();
        await Promise.all([
            loadCategories(),
            loadRecommend(),
            loadRanking(),
            loadNewRelease()
        ]);
    }

    init();
})();
