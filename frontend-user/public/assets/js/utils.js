/**
 * 工具函数
 */
const Utils = {
    // 格式化时间
    formatTime(seconds) {
        if (!seconds || isNaN(seconds)) return '0:00';
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    },

    // 格式化数字
    formatNumber(num) {
        if (num >= 10000) {
            return (num / 10000).toFixed(1) + '万';
        }
        return num.toString();
    },

    // 防抖
    debounce(fn, delay = 300) {
        let timer = null;
        return function(...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    },

    // 节流
    throttle(fn, delay = 300) {
        let last = 0;
        return function(...args) {
            const now = Date.now();
            if (now - last >= delay) {
                last = now;
                fn.apply(this, args);
            }
        };
    },

    // 获取URL参数
    getQueryParam(name) {
        const params = new URLSearchParams(window.location.search);
        return params.get(name);
    },

    // 设备检测
    getDevice() {
        const ua = navigator.userAgent.toLowerCase();
        if (ua.includes('miniprogram')) return 'miniprogram';
        if (ua.includes('micromessenger')) return 'wechat';
        if (/mobile|android|iphone|ipod|blackberry|windows phone/i.test(ua)) return 'mobile';
        if (/ipad|tablet|playbook|silk/i.test(ua)) return 'tablet';
        return 'pc';
    },

    // 本地存储
    storage: {
        get(key) {
            try {
                const value = localStorage.getItem(key);
                return value ? JSON.parse(value) : null;
            } catch {
                return null;
            }
        },
        set(key, value) {
            try {
                localStorage.setItem(key, JSON.stringify(value));
            } catch (e) {
                console.warn('Storage error:', e);
            }
        },
        remove(key) {
            localStorage.removeItem(key);
        }
    },

    // RSA加密
    crypto: {
        _publicKey: null,
        
        async getPublicKey() {
            if (this._publicKey) return this._publicKey;
            const res = await Api.auth.getPublicKey();
            if (res.code === 200) {
                this._publicKey = res.data.public_key;
                return this._publicKey;
            }
            throw new Error('获取公钥失败');
        },
        
        async encrypt(text) {
            try {
                const publicKey = await this.getPublicKey();
                const pemHeader = '-----BEGIN PUBLIC KEY-----';
                const pemFooter = '-----END PUBLIC KEY-----';
                const pemContents = publicKey.replace(pemHeader, '').replace(pemFooter, '').replace(/\s/g, '');
                const binaryDer = Uint8Array.from(atob(pemContents), c => c.charCodeAt(0));
                
                const cryptoKey = await window.crypto.subtle.importKey(
                    'spki',
                    binaryDer.buffer,
                    { name: 'RSA-OAEP', hash: 'SHA-1' },
                    false,
                    ['encrypt']
                );
                
                const encoded = new TextEncoder().encode(text);
                const encrypted = await window.crypto.subtle.encrypt(
                    { name: 'RSA-OAEP' },
                    cryptoKey,
                    encoded
                );
                
                return btoa(String.fromCharCode(...new Uint8Array(encrypted)));
            } catch (err) {
                console.error('加密失败:', err);
                throw err;
            }
        }
    }
};

// 全局暴露
window.Utils = Utils;
