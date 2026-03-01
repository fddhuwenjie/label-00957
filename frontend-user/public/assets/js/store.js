/**
 * 状态管理
 */
const Store = {
    // 用户状态
    user: {
        _data: null,
        _token: null,

        init() {
            this._token = Utils.storage.get('token');
            this._data = Utils.storage.get('user');
        },

        get isLoggedIn() {
            return !!this._token;
        },

        get data() {
            return this._data;
        },

        get token() {
            return this._token;
        },

        login(token, user) {
            this._token = token;
            this._data = user;
            Utils.storage.set('token', token);
            Utils.storage.set('user', user);
            this.updateUI();
        },

        logout() {
            this._token = null;
            this._data = null;
            Utils.storage.remove('token');
            Utils.storage.remove('user');
            this.updateUI();
        },

        updateUI() {
            const loggedEl = document.querySelector('.user-logged');
            const guestEl = document.querySelector('.user-guest');
            const avatarEl = document.getElementById('userAvatar');
            const nameEl = document.getElementById('userName');
            const emailEl = document.getElementById('userEmail');
            
            // 移动端菜单用户信息
            const mobileUserInfo = document.getElementById('mobileUserInfo');
            const mobileAvatar = document.querySelector('.mobile-avatar');
            const mobileUserName = document.querySelector('.mobile-user-name');
            
            // 检测是否为移动端
            const isMobile = window.innerWidth < 768;

            if (this.isLoggedIn && this._data) {
                if (loggedEl) loggedEl.style.display = 'flex';
                if (guestEl) guestEl.style.display = 'none';
                if (avatarEl) avatarEl.src = this._data.avatar || '/assets/images/avatar-default.svg';
                if (nameEl) nameEl.textContent = this._data.nickname || '用户';
                if (emailEl) emailEl.textContent = this._data.email || '';
                // 更新移动端菜单
                if (mobileUserInfo) mobileUserInfo.href = '/user';
                if (mobileAvatar) mobileAvatar.src = this._data.avatar || '/assets/images/avatar-default.svg';
                if (mobileUserName) mobileUserName.textContent = this._data.nickname || '用户';
            } else {
                if (loggedEl) loggedEl.style.display = 'none';
                // 移动端不显示登录/注册按钮
                if (guestEl) guestEl.style.display = isMobile ? 'none' : 'flex';
                // 重置移动端菜单
                if (mobileUserInfo) mobileUserInfo.href = '/login';
                if (mobileUserName) mobileUserName.textContent = '点击登录';
            }
        }
    },

    // 播放器状态
    player: {
        _current: null,
        _playlist: [],
        _index: 0,
        _isPlaying: false,

        get current() {
            return this._current;
        },

        get playlist() {
            return this._playlist;
        },

        get isPlaying() {
            return this._isPlaying;
        },

        setCurrent(music) {
            this._current = music;
        },

        setPlaylist(list, index = 0) {
            this._playlist = list;
            this._index = index;
        },

        setPlaying(playing) {
            this._isPlaying = playing;
        },

        next() {
            if (this._playlist.length === 0) return null;
            this._index = (this._index + 1) % this._playlist.length;
            return this._playlist[this._index];
        },

        prev() {
            if (this._playlist.length === 0) return null;
            this._index = (this._index - 1 + this._playlist.length) % this._playlist.length;
            return this._playlist[this._index];
        }
    },

    // 搜索历史
    searchHistory: {
        _key: 'search_history',
        _max: 10,

        get list() {
            return Utils.storage.get(this._key) || [];
        },

        add(keyword) {
            let list = this.list.filter(k => k !== keyword);
            list.unshift(keyword);
            if (list.length > this._max) {
                list = list.slice(0, this._max);
            }
            Utils.storage.set(this._key, list);
        },

        clear() {
            Utils.storage.remove(this._key);
        }
    }
};

// 初始化
Store.user.init();

// 立即更新UI，避免登录状态闪烁（scripts在body底部，DOM已就绪）
Store.user.updateUI();

window.Store = Store;
