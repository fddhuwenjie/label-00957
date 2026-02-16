/**
 * 排行榜页逻辑
 */
(function() {
    'use strict';

    let currentType = 'hot';

    // 加载排行榜
    async function loadRanking() {
        Components.loading.show();
        const res = await Api.music.ranking(currentType, 50);
        Components.loading.hide();

        if (res.code === 200 && res.data) {
            const body = document.getElementById('rankingBody');
            if (body) {
                body.innerHTML = res.data.map((m, i) => Components.renderRankingItem(m, i)).join('');
                Store.player.setPlaylist(res.data);
            }
        }
    }

    // 绑定事件
    function bindEvents() {
        // 榜单切换
        document.querySelectorAll('.ranking-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.ranking-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                currentType = tab.dataset.type;
                loadRanking();
            });
        });
    }

    // 初始化
    function init() {
        loadRanking();
        bindEvents();
    }

    init();
})();
