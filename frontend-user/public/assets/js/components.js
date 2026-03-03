/**
 * 组件
 */
const Components = {
    // Toast 提示
    toast(message, duration = 2000) {
        const el = document.getElementById('toast');
        if (!el) return;
        el.textContent = message;
        el.classList.add('show');
        setTimeout(() => el.classList.remove('show'), duration);
    },

    // Loading
    loading: {
        show() {
            const el = document.getElementById('loading');
            if (el) el.classList.add('show');
        },
        hide() {
            const el = document.getElementById('loading');
            if (el) el.classList.remove('show');
        },
        // 包装异步操作，保证最小300ms的loading效果
        async wrap(asyncFn) {
            this.show();
            const startTime = Date.now();
            try {
                return await asyncFn();
            } finally {
                const elapsed = Date.now() - startTime;
                if (elapsed < 300) {
                    await new Promise(r => setTimeout(r, 300 - elapsed));
                }
                this.hide();
            }
        }
    },

    // 渲染音乐卡片
    renderMusicCard(music) {
        const defaultCover = '/assets/images/cover-default.svg';
        const coverUrl = music.cover_url || defaultCover;
        return `
            <div class="music-card" data-id="${music.id}">
                <div class="music-card-cover">
                    <img src="${coverUrl}" alt="${music.title}" loading="lazy" onerror="this.onerror=null;this.src='${defaultCover}'">
                    <button class="music-card-play">
                        <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    </button>
                </div>
                <div class="music-card-info">
                    <div class="music-card-title">${music.title}</div>
                    <div class="music-card-artist">${music.artist}</div>
                </div>
            </div>
        `;
    },

    // 渲染音乐列表项
    renderMusicListItem(music, index = null) {
        const defaultCover = '/assets/images/cover-default.svg';
        const coverUrl = music.cover_url || defaultCover;
        const rankHtml = index !== null ? `<span class="music-list-rank ${index < 3 ? 'top' : ''}">${index + 1}</span>` : '';
        const isFavorited = window.FavoriteCache?.has(music.id);
        const favoriteClass = isFavorited ? 'active' : '';
        return `
            <div class="music-list-item" data-id="${music.id}">
                ${rankHtml}
                <img class="music-list-cover" src="${coverUrl}" alt="${music.title}" loading="lazy" onerror="this.onerror=null;this.src='${defaultCover}'">
                <div class="music-list-info">
                    <div class="music-list-title">${music.title}</div>
                    <div class="music-list-meta">${music.artist}${music.album ? ' · ' + music.album : ''}</div>
                </div>
                <span class="music-list-duration pc-only">${Utils.formatTime(music.duration)}</span>
                <div class="music-list-action">
                    <button class="btn-play" title="播放">
                        <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    </button>
                    <button class="btn-favorite ${favoriteClass}" title="${isFavorited ? '取消收藏' : '收藏'}">
                        <svg viewBox="0 0 24 24"><path d="M16.5 3c-1.74 0-3.41.81-4.5 2.09C10.91 3.81 9.24 3 7.5 3 4.42 3 2 5.42 2 8.5c0 3.78 3.4 6.86 8.55 11.54L12 21.35l1.45-1.32C18.6 15.36 22 12.28 22 8.5 22 5.42 19.58 3 16.5 3z"/></svg>
                    </button>
                </div>
            </div>
        `;
    },

    // 渲染分类标签
    renderCategoryTag(category) {
        return `
            <button class="filter-tab" data-id="${category.id}">
                ${category.name}
            </button>
        `;
    },

    // 渲染分类网格项
    renderCategoryGridItem(category) {
        const icons = {
            '流行': '🎵', '摇滚': '🎸', '民谣': '🎻',
            '电子': '🎹', '古典': '🎼', '嘻哈': '🎤'
        };
        return `
            <a href="/discover?category=${category.id}" class="category-item">
                <span class="category-icon">${icons[category.name] || '🎵'}</span>
                <span class="category-name">${category.name}</span>
            </a>
        `;
    },

    /**
     * 挂载完整分页组件
     * @param {string}   containerId  - 容器元素 ID
     * @param {number}   current      - 当前页码（从 1 开始）
     * @param {number}   totalPages   - 总页数
     * @param {Function} onChange     - 翻页回调 onChange(page)
     * @param {number}   [totalRecords] - 总记录数（可选，显示"共 N 条"）
     */
    mountPagination(containerId, current, totalPages, onChange, totalRecords) {
        const container = document.getElementById(containerId);
        if (!container) return;
        container.innerHTML = '';

        // 总条数
        if (totalRecords !== undefined) {
            const totalEl = document.createElement('span');
            totalEl.className = 'pag-total';
            totalEl.innerHTML = `共 <strong>${totalRecords}</strong> 条`;
            container.appendChild(totalEl);
        }

        if (totalPages <= 1) return;

        // ── 导航区 ──
        const navGroup = document.createElement('div');
        navGroup.className = 'pag-nav-group';

        const mkBtn = (html, title, page, disabled) => {
            const btn = document.createElement('button');
            btn.className = 'page-btn' + (title ? ' pag-nav' : '');
            btn.innerHTML = html;
            if (title) btn.title = title;
            btn.disabled = !!disabled;
            if (!disabled) btn.addEventListener('click', () => onChange(page));
            return btn;
        };

        // 首页
        navGroup.appendChild(mkBtn('|◀', '首页', 1, current <= 1));
        // 上一页
        navGroup.appendChild(mkBtn('◀', '上一页', current - 1, current <= 1));

        // ── 页码区 ──
        const pagesEl = document.createElement('div');
        pagesEl.className = 'pag-pages';

        const addPage = (p) => {
            const btn = document.createElement('button');
            btn.className = 'page-btn' + (p === current ? ' active' : '');
            btn.textContent = p;
            btn.addEventListener('click', () => onChange(p));
            pagesEl.appendChild(btn);
        };
        const addEllipsis = () => {
            const span = document.createElement('span');
            span.className = 'pag-ellipsis';
            span.textContent = '···';
            pagesEl.appendChild(span);
        };

        if (totalPages <= 7) {
            for (let i = 1; i <= totalPages; i++) addPage(i);
        } else {
            addPage(1);
            if (current > 4) addEllipsis();
            const start = Math.max(2, current - 2);
            const end = Math.min(totalPages - 1, current + 2);
            for (let i = start; i <= end; i++) addPage(i);
            if (current < totalPages - 3) addEllipsis();
            addPage(totalPages);
        }

        navGroup.appendChild(pagesEl);

        // 下一页
        navGroup.appendChild(mkBtn('▶', '下一页', current + 1, current >= totalPages));
        // 末页
        navGroup.appendChild(mkBtn('▶|', '末页', totalPages, current >= totalPages));

        container.appendChild(navGroup);

        // ── 页码信息 ──
        const infoEl = document.createElement('span');
        infoEl.className = 'pag-page-info';
        infoEl.innerHTML = `第 <strong>${current}</strong>/<strong>${totalPages}</strong> 页`;
        container.appendChild(infoEl);

        // ── 跳转输入框 ──
        const gotoEl = document.createElement('span');
        gotoEl.className = 'pag-goto';
        const gotoInput = document.createElement('input');
        gotoInput.type = 'number';
        gotoInput.className = 'pag-goto-input';
        gotoInput.min = 1;
        gotoInput.max = totalPages;
        gotoInput.placeholder = current;
        gotoInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                const p = parseInt(gotoInput.value);
                if (p >= 1 && p <= totalPages) {
                    onChange(p);
                    gotoInput.value = '';
                }
            }
        });
        gotoEl.appendChild(document.createTextNode('跳至 '));
        gotoEl.appendChild(gotoInput);
        gotoEl.appendChild(document.createTextNode(' 页'));
        container.appendChild(gotoEl);
    },

    // 渲染排行榜项
    renderRankingItem(music, index) {
        const defaultCover = '/assets/images/cover-default.svg';
        const coverUrl = music.cover_url || defaultCover;
        const rankClass = index < 3 ? 'rank-top' : '';
        return `
            <div class="ranking-item" data-id="${music.id}">
                <span class="ranking-rank ${rankClass}">${index + 1}</span>
                <img class="ranking-cover" src="${coverUrl}" alt="${music.title}" onerror="this.onerror=null;this.src='${defaultCover}'">
                <div class="ranking-info">
                    <div class="ranking-title">${music.title}</div>
                    <div class="ranking-artist">${music.artist}</div>
                </div>
                <span class="ranking-count pc-only">${Utils.formatNumber(music.play_count)}次播放</span>
                <span class="ranking-duration pc-only">${Utils.formatTime(music.duration)}</span>
                <div class="ranking-action">
                    <button class="btn-play" title="播放">
                        <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    </button>
                </div>
            </div>
        `;
    }
};

window.Components = Components;
