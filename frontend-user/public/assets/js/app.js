/**
 * 应用主入口
 */
(function() {
    'use strict';

    // 收藏状态缓存
    const FavoriteCache = {
        _favorites: new Set(),
        _loaded: false,

        async load() {
            if (this._loaded || !Store.user.isLoggedIn) return;
            try {
                const res = await Api.user.favorites({ page: 1, limit: 1000 });
                if (res.code === 200) {
                    const data = res.data.data || res.data || [];
                    this._favorites = new Set(data.map(f => f.music_id || f.music?.id));
                    this._loaded = true;
                }
            } catch (e) {
                console.log('Load favorites error:', e);
            }
        },

        has(musicId) {
            return this._favorites.has(Number(musicId));
        },

        add(musicId) {
            this._favorites.add(Number(musicId));
        },

        remove(musicId) {
            this._favorites.delete(Number(musicId));
        },

        reset() {
            this._favorites.clear();
            this._loaded = false;
        }
    };

    // 播放器控制器
    const Player = {
        audio: null,
        elements: {},

        init() {
            this.audio = document.getElementById('audioElement');
            if (!this.audio) return;

            this.elements = {
                player: document.getElementById('player'),
                cover: document.getElementById('playerCover'),
                title: document.getElementById('playerTitle'),
                artist: document.getElementById('playerArtist'),
                currentTime: document.getElementById('playerCurrentTime'),
                duration: document.getElementById('playerDuration'),
                progressBar: document.getElementById('progressBar'),
                progressPlayed: document.getElementById('progressPlayed'),
                progressHandle: document.getElementById('progressHandle'),
                btnPlay: document.getElementById('btnPlay'),
                btnPrev: document.getElementById('btnPrev'),
                btnNext: document.getElementById('btnNext'),
                btnFavorite: document.getElementById('btnFavorite'),
                volumeSlider: document.getElementById('volumeSlider')
            };

            this.bindEvents();
            this.loadVolume();
        },

        bindEvents() {
            const { audio, elements } = this;

            // 播放/暂停
            elements.btnPlay?.addEventListener('click', () => this.togglePlay());

            // 上一首/下一首
            elements.btnPrev?.addEventListener('click', () => this.playPrev());
            elements.btnNext?.addEventListener('click', () => this.playNext());

            // 收藏按钮
            elements.btnFavorite?.addEventListener('click', () => this.toggleFavorite());

            // 进度条点击
            elements.progressBar?.addEventListener('click', (e) => {
                const rect = elements.progressBar.getBoundingClientRect();
                const percent = (e.clientX - rect.left) / rect.width;
                if (audio.duration) {
                    audio.currentTime = percent * audio.duration;
                }
            });

            // 进度条拖拽
            let isDragging = false;
            elements.progressBar?.addEventListener('mousedown', () => isDragging = true);
            document.addEventListener('mouseup', () => isDragging = false);
            document.addEventListener('mousemove', (e) => {
                if (!isDragging || !elements.progressBar) return;
                const rect = elements.progressBar.getBoundingClientRect();
                let percent = (e.clientX - rect.left) / rect.width;
                percent = Math.max(0, Math.min(1, percent));
                if (audio.duration) {
                    audio.currentTime = percent * audio.duration;
                }
            });

            // 音量
            elements.volumeSlider?.addEventListener('input', (e) => {
                audio.volume = e.target.value / 100;
                Utils.storage.set('volume', e.target.value);
            });

            // 音频事件
            audio.addEventListener('timeupdate', () => this.updateProgress());
            audio.addEventListener('loadedmetadata', () => this.updateDuration());
            audio.addEventListener('ended', () => this.playNext());
            audio.addEventListener('play', () => this.updatePlayState(true));
            audio.addEventListener('pause', () => this.updatePlayState(false));
            audio.addEventListener('error', (e) => {
                console.log('Audio error:', e);
                Components.toast('音频加载失败，请稍后重试');
            });
        },

        loadVolume() {
            const volume = Utils.storage.get('volume') || 80;
            if (this.audio) this.audio.volume = volume / 100;
            if (this.elements.volumeSlider) this.elements.volumeSlider.value = volume;
        },

        play(music) {
            if (!music || !this.audio) return;

            // 检查音频URL
            if (!music.audio_url) {
                Components.toast('暂无音频资源');
                return;
            }

            Store.player.setCurrent(music);
            this.audio.src = music.audio_url;
            this.audio.play().catch(e => {
                console.log('Play error:', e);
                Components.toast('播放失败，请稍后重试');
            });

            // 更新UI
            const defaultCover = '/assets/images/cover-default.svg';
            this.elements.player?.classList.add('active');
            this.elements.cover.src = music.cover_url || defaultCover;
            this.elements.cover.onerror = function() {
                this.onerror = null;
                this.src = defaultCover;
            };
            this.elements.title.textContent = music.title;
            this.elements.artist.textContent = music.artist;

            // 更新收藏按钮状态
            this.updateFavoriteState(music.id);

            // 记录播放
            Api.music.play(music.id);
        },

        togglePlay() {
            if (!this.audio) return;
            if (!this.audio.src || this.audio.src === window.location.href) {
                Components.toast('请先选择音乐');
                return;
            }
            if (this.audio.paused) {
                this.audio.play().catch(e => console.log('Play error:', e));
            } else {
                this.audio.pause();
            }
        },

        playPrev() {
            const music = Store.player.prev();
            if (music) this.play(music);
        },

        playNext() {
            const music = Store.player.next();
            if (music) this.play(music);
        },

        updateProgress() {
            const { audio, elements } = this;
            if (!audio.duration) return;

            const percent = (audio.currentTime / audio.duration) * 100;
            if (elements.progressPlayed) elements.progressPlayed.style.width = `${percent}%`;
            if (elements.progressHandle) elements.progressHandle.style.left = `${percent}%`;
            if (elements.currentTime) elements.currentTime.textContent = Utils.formatTime(audio.currentTime);
        },

        updateDuration() {
            if (this.elements.duration) {
                this.elements.duration.textContent = Utils.formatTime(this.audio.duration);
            }
        },

        updatePlayState(playing) {
            Store.player.setPlaying(playing);
            const playIcon = this.elements.btnPlay?.querySelector('.icon-play');
            const pauseIcon = this.elements.btnPlay?.querySelector('.icon-pause');
            if (playIcon) playIcon.style.display = playing ? 'none' : 'block';
            if (pauseIcon) pauseIcon.style.display = playing ? 'block' : 'none';
        },

        // 更新收藏按钮状态
        updateFavoriteState(musicId) {
            const btn = this.elements.btnFavorite;
            if (!btn) return;
            
            const isFavorited = FavoriteCache.has(musicId);
            btn.classList.toggle('active', isFavorited);
            btn.title = isFavorited ? '取消收藏' : '收藏';
        },

        // 切换收藏状态
        async toggleFavorite() {
            if (!Store.user.isLoggedIn) {
                Components.toast('请先登录');
                return;
            }

            const music = Store.player.current;
            if (!music) {
                Components.toast('请先播放音乐');
                return;
            }

            const musicId = music.id;
            const isFavorited = FavoriteCache.has(musicId);

            try {
                let res;
                if (isFavorited) {
                    res = await Api.user.removeFavorite(musicId);
                    if (res.code === 200) {
                        FavoriteCache.remove(musicId);
                        Components.toast('已取消收藏');
                    }
                } else {
                    res = await Api.user.addFavorite(musicId);
                    if (res.code === 200) {
                        FavoriteCache.add(musicId);
                        Components.toast('收藏成功');
                    }
                }

                if (res.code !== 200) {
                    Components.toast(res.message || '操作失败');
                }

                this.updateFavoriteState(musicId);
            } catch (e) {
                console.log('Favorite error:', e);
                Components.toast('操作失败');
            }
        }
    };

    // 全局事件绑定
    function bindGlobalEvents() {
        // 搜索框
        const headerSearch = document.getElementById('headerSearch');
        headerSearch?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && e.target.value.trim()) {
                window.location.href = `/search?keyword=${encodeURIComponent(e.target.value.trim())}`;
            }
        });

        // 移动端菜单
        const menuToggle = document.getElementById('menuToggle');
        const mobileMenu = document.getElementById('mobileMenu');
        const menuOverlay = document.getElementById('menuOverlay');

        menuToggle?.addEventListener('click', () => {
            mobileMenu?.classList.toggle('active');
            menuOverlay?.classList.toggle('active');
        });

        menuOverlay?.addEventListener('click', () => {
            mobileMenu?.classList.remove('active');
            menuOverlay?.classList.remove('active');
        });

        // 音乐卡片点击播放和收藏
        document.addEventListener('click', async (e) => {
            // 收藏按钮点击
            const favoriteBtn = e.target.closest('.btn-favorite');
            if (favoriteBtn) {
                e.preventDefault();
                e.stopPropagation();
                const item = favoriteBtn.closest('[data-id]');
                const musicId = item?.dataset.id;
                if (musicId) {
                    await toggleMusicFavorite(musicId, favoriteBtn);
                }
                return;
            }

            // 播放按钮或卡片点击
            const card = e.target.closest('.music-card');
            const listItem = e.target.closest('.music-list-item');
            const rankingItem = e.target.closest('.ranking-item');
            const playBtn = e.target.closest('.btn-play, .music-card-play');

            if (playBtn || card || listItem || rankingItem) {
                const el = card || listItem || rankingItem;
                const id = el?.dataset.id;
                if (id) {
                    playMusicById(id);
                }
            }
        });
    }

    // 切换音乐收藏状态
    async function toggleMusicFavorite(musicId, btn) {
        if (!Store.user.isLoggedIn) {
            Components.toast('请先登录');
            return;
        }

        const isFavorited = FavoriteCache.has(musicId);

        try {
            let res;
            if (isFavorited) {
                res = await Api.user.removeFavorite(musicId);
                if (res.code === 200) {
                    FavoriteCache.remove(musicId);
                    btn?.classList.remove('active');
                    Components.toast('已取消收藏');
                }
            } else {
                res = await Api.user.addFavorite(musicId);
                if (res.code === 200) {
                    FavoriteCache.add(musicId);
                    btn?.classList.add('active');
                    Components.toast('收藏成功');
                }
            }

            if (res.code !== 200) {
                Components.toast(res.message || '操作失败');
            }

            // 同步更新播放器收藏按钮状态
            if (Store.player.current?.id == musicId) {
                Player.updateFavoriteState(musicId);
            }
        } catch (e) {
            console.log('Favorite error:', e);
            Components.toast('操作失败');
        }
    }

    // 播放指定ID的音乐
    async function playMusicById(id) {
        Components.loading.show();
        const res = await Api.music.detail(id);
        Components.loading.hide();

        if (res.code === 200) {
            Player.play(res.data);
        } else {
            Components.toast(res.message || '加载失败');
        }
    }

    // 初始化
    async function init() {
        Player.init();
        Store.user.updateUI();
        bindGlobalEvents();
        
        // 加载收藏列表缓存
        if (Store.user.isLoggedIn) {
            await FavoriteCache.load();
        }
    }

    // DOM Ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // 全局暴露
    window.Player = Player;
    window.playMusicById = playMusicById;
    window.FavoriteCache = FavoriteCache;
    window.toggleMusicFavorite = toggleMusicFavorite;
})();
