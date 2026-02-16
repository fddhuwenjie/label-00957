/**
 * API 请求封装
 */
const Api = {
    baseUrl: '/api',

    // 请求方法
    async request(url, options = {}) {
        const token = Utils.storage.get('token');
        const headers = {
            'Content-Type': 'application/json',
            ...options.headers
        };

        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        try {
            const response = await fetch(this.baseUrl + url, {
                ...options,
                headers
            });

            const data = await response.json();

            if (data.code === 401) {
                Utils.storage.remove('token');
                Utils.storage.remove('user');
                window.location.href = '/login';
                return;
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            return { code: 500, message: '网络错误，请稍后重试' };
        }
    },

    get(url, params = {}) {
        const query = new URLSearchParams(params).toString();
        const fullUrl = query ? `${url}?${query}` : url;
        return this.request(fullUrl, { method: 'GET' });
    },

    post(url, data = {}) {
        return this.request(url, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },

    put(url, data = {}) {
        return this.request(url, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },

    delete(url) {
        return this.request(url, { method: 'DELETE' });
    },

    // 认证相关
    auth: {
        getPublicKey: () => Api.get('/auth/public-key'),
        login: (data) => Api.post('/auth/login', data),
        register: (data) => Api.post('/auth/register', data)
    },

    // 音乐相关
    music: {
        list: (params) => Api.get('/music/list', params),
        detail: (id) => Api.get('/music/detail', { id }),
        search: (params) => Api.get('/music/search', params),
        recommend: (limit) => Api.get('/music/recommend', { limit }),
        ranking: (type, limit) => Api.get('/music/ranking', { type, limit }),
        categories: () => Api.get('/music/categories'),
        play: (id) => Api.post('/music/play', { id })
    },

    // 用户相关
    user: {
        profile: () => Api.get('/user/profile'),
        updateProfile: (data) => Api.put('/user/profile', data),
        favorites: (params) => Api.get('/user/favorites', params),
        addFavorite: (musicId) => Api.post('/user/favorite/add', { music_id: musicId }),
        removeFavorite: (musicId) => Api.delete(`/user/favorite/${musicId}`),
        history: (params) => Api.get('/user/history', params)
    }
};

window.Api = Api;
