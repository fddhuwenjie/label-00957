/**
 * 播放页逻辑
 */
(function() {
    'use strict';

    const elements = {
        bg: document.getElementById('playBg'),
        discWrapper: document.getElementById('discWrapper'),
        discCover: document.getElementById('discCover'),
        title: document.getElementById('playTitle'),
        artist: document.getElementById('playArtist'),
        album: document.getElementById('playAlbum'),
        lyricsContent: document.getElementById('lyricsContent'),
        actionFavorite: document.getElementById('actionFavorite'),
        actionShare: document.getElementById('actionShare')
    };

    let currentMusic = null;
    let animationId = null;

    // 加载音乐详情
    async function loadMusic() {
        const page = document.querySelector('.page-play');
        const musicId = page?.dataset.musicId;
        
        if (!musicId || musicId === '0') {
            // 尝试从播放器获取当前音乐
            currentMusic = Store.player.current;
            if (currentMusic) {
                renderMusic(currentMusic);
            } else {
                if (elements.title) elements.title.textContent = '请选择音乐播放';
            }
            return;
        }

        Components.loading.show();
        const res = await Api.music.detail(musicId);
        Components.loading.hide();

        if (res.code === 200) {
            currentMusic = res.data;
            renderMusic(currentMusic);
            Player.play(currentMusic);
        } else {
            Components.toast(res.message || '加载失败');
        }
    }

    // 渲染音乐信息
    function renderMusic(music) {
        const defaultCover = '/assets/images/cover-default.svg';
        const coverUrl = music.cover_url || defaultCover;
        
        if (elements.discCover) {
            elements.discCover.src = coverUrl;
            elements.discCover.onerror = function() {
                this.onerror = null;
                this.src = defaultCover;
            };
        }
        if (elements.title) elements.title.textContent = music.title;
        if (elements.artist) elements.artist.textContent = music.artist;
        if (elements.album) elements.album.textContent = music.album || '未知专辑';

        // 背景模糊
        if (elements.bg) elements.bg.style.backgroundImage = `url(${coverUrl})`;

        // 歌词
        if (music.lyrics && elements.lyricsContent) {
            renderLyrics(music.lyrics);
        }

        // 更新收藏按钮状态
        updateFavoriteState(music.id);

        // 唱片旋转动画
        startDiscAnimation();
    }

    // 渲染歌词
    function renderLyrics(lyrics) {
        const lines = lyrics.split('\n').filter(l => l.trim());
        elements.lyricsContent.innerHTML = lines.map(line => 
            `<p class="lyrics-line">${line.replace(/\[\d+:\d+\.\d+\]/g, '')}</p>`
        ).join('');
    }

    // 唱片旋转动画
    function startDiscAnimation() {
        if (animationId) {
            cancelAnimationFrame(animationId);
        }
        
        let rotation = 0;
        const animate = () => {
            if (Store.player.isPlaying && elements.discWrapper) {
                rotation += 0.5;
                elements.discWrapper.style.transform = `rotate(${rotation}deg)`;
            }
            animationId = requestAnimationFrame(animate);
        };
        animate();
    }

    // 更新收藏按钮状态
    function updateFavoriteState(musicId) {
        if (!elements.actionFavorite) return;
        
        const isFavorited = window.FavoriteCache?.has(musicId);
        elements.actionFavorite.classList.toggle('active', isFavorited);
        elements.actionFavorite.title = isFavorited ? '取消收藏' : '收藏';
    }

    // 切换收藏状态
    async function toggleFavorite() {
        if (!Store.user.isLoggedIn) {
            Components.toast('请先登录');
            return;
        }
        if (!currentMusic) {
            Components.toast('请先播放音乐');
            return;
        }

        const musicId = currentMusic.id;
        const isFavorited = window.FavoriteCache?.has(musicId);

        try {
            let res;
            if (isFavorited) {
                res = await Api.user.removeFavorite(musicId);
                if (res.code === 200) {
                    window.FavoriteCache?.remove(musicId);
                    Components.toast('已取消收藏');
                }
            } else {
                res = await Api.user.addFavorite(musicId);
                if (res.code === 200) {
                    window.FavoriteCache?.add(musicId);
                    Components.toast('收藏成功');
                }
            }

            if (res.code !== 200) {
                Components.toast(res.message || '操作失败');
            }

            updateFavoriteState(musicId);
            
            // 同步更新播放器收藏按钮
            if (window.Player) {
                Player.updateFavoriteState(musicId);
            }
        } catch (e) {
            console.log('Favorite error:', e);
            Components.toast('操作失败');
        }
    }

    // 绑定事件
    function bindEvents() {
        // 收藏
        elements.actionFavorite?.addEventListener('click', toggleFavorite);

        // 分享
        elements.actionShare?.addEventListener('click', () => {
            if (navigator.share && currentMusic) {
                navigator.share({
                    title: currentMusic.title,
                    text: `${currentMusic.title} - ${currentMusic.artist}`,
                    url: window.location.href
                });
            } else {
                // 复制链接
                navigator.clipboard?.writeText(window.location.href);
                Components.toast('链接已复制');
            }
        });
    }

    // 初始化
    function init() {
        loadMusic();
        bindEvents();
    }

    init();
})();
