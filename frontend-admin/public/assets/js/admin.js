/**
 * 管理后台主脚本
 */
(function() {
    'use strict';

    const API_BASE = '/api';
    let currentPage = 'dashboard';

    // 各列表页状态
    const _state = {
        music: { page: 1, keyword: '' },
        user:  { page: 1, keyword: '' },
        log:   { page: 1 }
    };

    // API请求
    async function api(url, options = {}) {
        const token = localStorage.getItem('admin_token');
        const headers = { ...options.headers };
        // 非 FormData 请求才设置 Content-Type
        if (!(options.body instanceof FormData)) {
            headers['Content-Type'] = 'application/json';
        }
        if (token) headers['Authorization'] = `Bearer ${token}`;

        try {
            const res = await fetch(API_BASE + url, { ...options, headers });
            const data = await res.json();
            if (data.code === 401) {
                localStorage.removeItem('admin_token');
                window.location.href = '/login';
                return;
            }
            return data;
        } catch (e) {
            return { code: 500, message: '网络错误' };
        }
    }

    const get = (url, params = {}) => {
        const query = new URLSearchParams(params).toString();
        return api(query ? `${url}?${query}` : url);
    };

    const post = (url, data) => api(url, { method: 'POST', body: JSON.stringify(data) });
    const put = (url, data) => api(url, { method: 'PUT', body: JSON.stringify(data) });
    const del = (url) => api(url, { method: 'DELETE' });

    // 文件上传（FormData）
    const upload = (url, formData) => api(url, { method: 'POST', body: formData });

    // Toast
    function toast(msg, duration = 2000) {
        const el = document.getElementById('toast');
        el.textContent = msg;
        el.classList.add('show');
        setTimeout(() => el.classList.remove('show'), duration);
    }

    // Modal
    const modal = {
        el: document.getElementById('modal'),
        show(title, body, onConfirm) {
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalBody').innerHTML = body;
            this.el.classList.add('show');
            this.onConfirm = onConfirm;
        },
        hide() { this.el.classList.remove('show'); }
    };

    // 现代确认弹窗
    const confirmDialog = {
        overlay: null,
        init() {
            if (this.overlay) return;
            this.overlay = document.createElement('div');
            this.overlay.className = 'confirm-overlay';
            this.overlay.innerHTML = `
                <div class="confirm-box">
                    <div class="confirm-icon danger">⚠</div>
                    <div class="confirm-title">确认删除</div>
                    <div class="confirm-message"></div>
                    <div class="confirm-actions">
                        <button class="confirm-btn confirm-btn-cancel">取消</button>
                        <button class="confirm-btn confirm-btn-danger">确认删除</button>
                    </div>
                </div>
            `;
            document.body.appendChild(this.overlay);
            
            // 点击遮罩关闭
            this.overlay.addEventListener('click', (e) => {
                if (e.target === this.overlay) this.hide();
            });
        },
        show(message, title = '确认删除') {
            this.init();
            this.overlay.querySelector('.confirm-title').textContent = title;
            this.overlay.querySelector('.confirm-message').textContent = message;
            this.overlay.classList.add('show');
            
            return new Promise((resolve) => {
                const cancelBtn = this.overlay.querySelector('.confirm-btn-cancel');
                const confirmBtn = this.overlay.querySelector('.confirm-btn-danger');
                
                const cleanup = () => {
                    cancelBtn.onclick = null;
                    confirmBtn.onclick = null;
                };
                
                cancelBtn.onclick = () => {
                    cleanup();
                    this.hide();
                    resolve(false);
                };
                
                confirmBtn.onclick = () => {
                    cleanup();
                    this.hide();
                    resolve(true);
                };
            });
        },
        hide() {
            if (this.overlay) this.overlay.classList.remove('show');
        }
    };

    // 页面渲染
    const pages = {
        async dashboard() {
            const stats = await get('/dashboard/stats');
            const recent = await get('/dashboard/recent-music');
            const top = await get('/dashboard/top-music');

            const s = stats.data || {};
            return `
                <h2 style="margin-bottom:20px">仪表盘</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value">${s.music_count || 0}</div>
                        <div class="stat-label">音乐总数</div>
                        <div class="stat-change">今日新增 ${s.today_music || 0}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${s.user_count || 0}</div>
                        <div class="stat-label">用户总数</div>
                        <div class="stat-change">今日新增 ${s.today_users || 0}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${s.category_count || 0}</div>
                        <div class="stat-label">分类数量</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${formatNumber(s.total_plays || 0)}</div>
                        <div class="stat-label">总播放量</div>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                    <div class="card">
                        <div class="card-header"><span class="card-title">最新音乐</span></div>
                        <table class="table">
                            <thead><tr><th>标题</th><th>歌手</th></tr></thead>
                            <tbody>${(recent.data || []).length ? (recent.data || []).map(m => `<tr><td>${m.title}</td><td>${m.artist}</td></tr>`).join('') : emptyRow(2)}</tbody>
                        </table>
                    </div>
                    <div class="card">
                        <div class="card-header"><span class="card-title">热门音乐</span></div>
                        <table class="table">
                            <thead><tr><th>标题</th><th>播放量</th></tr></thead>
                            <tbody>${(top.data || []).length ? (top.data || []).map(m => `<tr><td>${m.title}</td><td>${formatNumber(m.play_count)}</td></tr>`).join('') : emptyRow(2)}</tbody>
                        </table>
                    </div>
                </div>
            `;
        },

        async music() {
            _state.music.page = 1;
            _state.music.keyword = '';
            const res = await get('/music/list', { page: 1, limit: 20 });
            const cats = await get('/category/list', { page: 0 });
            const list = res.data?.data || res.data || [];
            const total = res.data?.total || list.length;
            const totalPages = Math.ceil(total / 20) || 1;
            const categories = cats.data || [];

            window._categories = categories;

            return `
                <h2 style="margin-bottom:20px">音乐管理</h2>
                <div class="toolbar">
                    <div class="search-box">
                        <input type="text" class="search-input" id="musicSearch" placeholder="搜索音乐">
                        <button class="btn btn-outline" onclick="searchMusic()">搜索</button>
                    </div>
                    <button class="btn btn-primary" onclick="showMusicForm()">+ 新增音乐</button>
                </div>
                <div class="card">
                    <table class="table">
                        <thead><tr><th>ID</th><th>标题</th><th>歌手</th><th>分类</th><th>播放量</th><th>状态</th><th>操作</th></tr></thead>
                        <tbody id="musicTable">${renderMusicRows(list)}</tbody>
                    </table>
                    <div class="pagination" id="musicPagination">${renderPagination(1, totalPages, 'loadMusicPage', total)}</div>
                </div>
            `;
        },

        async category() {
            const res = await get('/category/list', { page: 1, limit: 20 });
            const list = res.data?.data || res.data || [];
            const total = res.data?.total || list.length;
            const perPage = res.data?.per_page || 20;
            const totalPages = Math.ceil(total / perPage);

            return `
                <h2 style="margin-bottom:20px">分类管理</h2>
                <div class="toolbar">
                    <div class="search-box">
                        <input type="text" class="search-input" id="categorySearch" placeholder="搜索分类">
                        <button class="btn btn-outline" onclick="searchCategory()">搜索</button>
                    </div>
                    <button class="btn btn-primary" onclick="showCategoryForm()">+ 新增分类</button>
                </div>
                <div class="card">
                    <table class="table">
                        <thead><tr><th>ID</th><th>名称</th><th>排序</th><th>状态</th><th>操作</th></tr></thead>
                        <tbody id="categoryTable">${renderCategoryRows(list)}</tbody>
                    </table>
                    <div class="pagination" id="categoryPagination">${renderPagination(1, totalPages, 'loadCategoryPage', total)}</div>
                </div>
            `;
        },

        async user() {
            _state.user.page = 1;
            _state.user.keyword = '';
            const res = await get('/user/list', { page: 1, limit: 20 });
            const list = res.data?.data || res.data || [];
            const total = res.data?.total || list.length;
            const totalPages = Math.ceil(total / 20) || 1;

            return `
                <h2 style="margin-bottom:20px">用户管理</h2>
                <div class="toolbar">
                    <div class="search-box">
                        <input type="text" class="search-input" id="userSearch" placeholder="搜索用户邮箱/昵称">
                        <button class="btn btn-outline" onclick="searchUser()">搜索</button>
                    </div>
                </div>
                <div class="card">
                    <table class="table">
                        <thead><tr><th>ID</th><th>邮箱</th><th>昵称</th><th>状态</th><th>注册时间</th><th>操作</th></tr></thead>
                        <tbody id="userTable">${renderUserRows(list)}</tbody>
                    </table>
                    <div class="pagination" id="userPagination">${renderPagination(1, totalPages, 'loadUserPage', total)}</div>
                </div>
            `;
        },

        async log() {
            _state.log.page = 1;
            const res = await get('/log/list', { page: 1, limit: 20 });
            const list = res.data?.data || res.data || [];
            const total = res.data?.total || list.length;
            const totalPages = Math.ceil(total / 20) || 1;

            return `
                <h2 style="margin-bottom:20px">操作日志</h2>
                <div class="card">
                    <table class="table">
                        <thead><tr><th>时间</th><th>管理员</th><th>模块</th><th>操作</th><th>内容</th><th>IP</th></tr></thead>
                        <tbody id="logTable">${renderLogRows(list)}</tbody>
                    </table>
                    <div class="pagination" id="logPagination">${renderPagination(1, totalPages, 'loadLogPage', total)}</div>
                </div>
            `;
        }
    };

    // 辅助函数
    function formatNumber(n) {
        return n >= 10000 ? (n / 10000).toFixed(1) + '万' : n;
    }

    function emptyRow(colspan) {
        return `<tr><td colspan="${colspan}"><div class="table-empty"><div class="table-empty-icon">📭</div><div class="table-empty-text">暂无数据</div></div></td></tr>`;
    }

    function renderUserRows(list) {
        if (!list || !list.length) return emptyRow(6);
        return list.map(u => `
            <tr>
                <td>${u.id}</td>
                <td>${u.email}</td>
                <td>${u.nickname || '-'}</td>
                <td><span class="status ${u.status === 1 ? 'status-success' : 'status-danger'}">${u.status === 1 ? '正常' : '禁用'}</span></td>
                <td>${u.created_at || '-'}</td>
                <td>
                    <button class="btn btn-sm ${u.status === 1 ? 'btn-danger' : 'btn-primary'}" onclick="toggleUserStatus(${u.id}, ${u.status === 1 ? 0 : 1})">${u.status === 1 ? '禁用' : '启用'}</button>
                </td>
            </tr>
        `).join('');
    }

    function renderLogRows(list) {
        if (!list || !list.length) return emptyRow(6);
        return list.map(l => `
            <tr>
                <td>${l.created_at}</td>
                <td>${l.admin?.nickname || l.admin?.username || '-'}</td>
                <td>${l.module}</td>
                <td>${l.action}</td>
                <td>${l.content || '-'}</td>
                <td>${l.ip || '-'}</td>
            </tr>
        `).join('');
    }

    function renderMusicRows(list) {
        if (!list || !list.length) return emptyRow(7);
        return list.map(m => `
            <tr>
                <td>${m.id}</td>
                <td>${m.title}</td>
                <td>${m.artist}</td>
                <td>${m.category?.name || '-'}</td>
                <td>${formatNumber(m.play_count)}</td>
                <td><span class="status ${m.status === 1 ? 'status-success' : 'status-danger'}">${m.status === 1 ? '上架' : '下架'}</span></td>
                <td>
                    <button class="btn btn-sm btn-outline" onclick="editMusic(${m.id})">编辑</button>
                    <button class="btn btn-sm ${m.status === 1 ? 'btn-danger' : 'btn-primary'}" onclick="toggleMusicStatus(${m.id}, ${m.status === 1 ? 0 : 1})">${m.status === 1 ? '下架' : '上架'}</button>
                </td>
            </tr>
        `).join('');
    }

    // 音乐操作 - 临时存储上传结果
    let _uploadedAudio = { url: '', duration: 0 };
    let _uploadedCover = { url: '' };

    window.showMusicForm = function(music = null) {
        const cats = window._categories || [];
        const catOptions = cats.map(c => `<option value="${c.id}" ${music?.category_id === c.id ? 'selected' : ''}>${c.name}</option>`).join('');
        
        // 重置上传状态
        _uploadedAudio = { url: music?.audio_url || '', duration: music?.duration || 0 };
        _uploadedCover = { url: music?.cover_url || '' };

        modal.show(music ? '编辑音乐' : '新增音乐', `
            <form id="musicForm">
                <input type="hidden" id="musicId" value="${music?.id || ''}">
                <div class="form-group">
                    <label class="form-label">标题 *</label>
                    <input type="text" class="form-input" id="musicTitle" value="${music?.title || ''}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">歌手 *</label>
                    <input type="text" class="form-input" id="musicArtist" value="${music?.artist || ''}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">专辑</label>
                    <input type="text" class="form-input" id="musicAlbum" value="${music?.album || ''}">
                </div>
                <div class="form-group">
                    <label class="form-label">分类</label>
                    <select class="form-select" id="musicCategory">${catOptions}</select>
                </div>
                <div class="form-group">
                    <label class="form-label">音频文件 *${music ? '（重新上传可替换）' : ''}</label>
                    <div class="upload-area" id="audioUploadArea">
                        <input type="file" id="audioFile" accept=".mp3,.wav,.flac,.aac,.ogg,.m4a" style="display:none">
                        <div class="upload-content" id="audioUploadContent">
                            ${music?.audio_url 
                                ? `<div class="upload-done">✅ 已有音频文件<br><small>${music.audio_url.split('/').pop()}</small></div>` 
                                : '<div class="upload-icon">🎵</div><div class="upload-text">点击或拖拽上传音频</div>'}
                            <div class="upload-hint">支持 mp3/wav/flac/aac/ogg/m4a，最大 20MB</div>
                        </div>
                        <div class="upload-progress" id="audioProgress" style="display:none">
                            <div class="upload-progress-bar" id="audioProgressBar"></div>
                        </div>
                    </div>
                    <div id="audioDurationInfo" style="margin-top:6px;color:#6b7280;font-size:12px">
                        ${music?.duration ? '时长: ' + Math.floor(music.duration/60) + ':' + String(music.duration%60).padStart(2,'0') : ''}
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">封面图片${music ? '（重新上传可替换）' : ''}</label>
                    <div class="upload-area upload-area-small" id="coverUploadArea">
                        <input type="file" id="coverFile" accept=".jpg,.jpeg,.png,.gif,.webp" style="display:none">
                        <div class="upload-content" id="coverUploadContent">
                            ${music?.cover_url && !music.cover_url.includes('default')
                                ? `<img src="${music.cover_url}" class="upload-preview-img" alt="封面">`
                                : '<div class="upload-icon">🖼️</div><div class="upload-text">点击或拖拽上传封面</div>'}
                            <div class="upload-hint">支持 jpg/png/gif/webp，最大 2MB</div>
                        </div>
                    </div>
                </div>
            </form>
        `, saveMusic);

        // 绑定上传事件
        setTimeout(() => {
            const audioArea = document.getElementById('audioUploadArea');
            const coverArea = document.getElementById('coverUploadArea');
            const audioInput = document.getElementById('audioFile');
            const coverInput = document.getElementById('coverFile');

            audioArea.addEventListener('click', () => audioInput.click());
            coverArea.addEventListener('click', () => coverInput.click());

            // 拖拽支持
            [audioArea, coverArea].forEach(area => {
                area.addEventListener('dragover', e => { e.preventDefault(); area.classList.add('drag-over'); });
                area.addEventListener('dragleave', () => area.classList.remove('drag-over'));
            });

            audioArea.addEventListener('drop', e => {
                e.preventDefault();
                audioArea.classList.remove('drag-over');
                if (e.dataTransfer.files.length) handleAudioUpload(e.dataTransfer.files[0]);
            });

            coverArea.addEventListener('drop', e => {
                e.preventDefault();
                coverArea.classList.remove('drag-over');
                if (e.dataTransfer.files.length) handleCoverUpload(e.dataTransfer.files[0]);
            });

            audioInput.addEventListener('change', () => {
                if (audioInput.files.length) handleAudioUpload(audioInput.files[0]);
            });

            coverInput.addEventListener('change', () => {
                if (coverInput.files.length) handleCoverUpload(coverInput.files[0]);
            });
        }, 100);
    };

    async function handleAudioUpload(file) {
        const allowExts = ['mp3', 'wav', 'flac', 'aac', 'ogg', 'm4a'];
        const ext = file.name.split('.').pop().toLowerCase();
        if (!allowExts.includes(ext)) {
            toast('仅支持 mp3/wav/flac/aac/ogg/m4a 格式');
            return;
        }
        if (file.size > 20 * 1024 * 1024) {
            toast('音频文件不能超过 20MB');
            return;
        }

        const content = document.getElementById('audioUploadContent');
        content.innerHTML = '<div class="upload-icon">⏳</div><div class="upload-text">上传中...</div>';

        const fd = new FormData();
        fd.append('audio', file);

        const res = await upload('/music/upload-audio', fd);
        if (res && res.code === 200) {
            _uploadedAudio = { url: res.data.url, duration: res.data.duration || 0 };
            content.innerHTML = `<div class="upload-done">✅ 上传成功<br><small>${file.name}</small></div><div class="upload-hint">点击可重新上传</div>`;
            
            // 显示时长
            const durationInfo = document.getElementById('audioDurationInfo');
            if (res.data.duration > 0) {
                const m = Math.floor(res.data.duration / 60);
                const s = String(res.data.duration % 60).padStart(2, '0');
                durationInfo.textContent = '时长: ' + m + ':' + s + '（自动获取）';
            } else {
                // 服务端无法获取时长，尝试前端获取
                tryGetDurationFromBrowser(file, durationInfo);
            }
            toast('音频上传成功');
        } else {
            content.innerHTML = '<div class="upload-icon">🎵</div><div class="upload-text">上传失败，点击重试</div><div class="upload-hint">支持 mp3/wav/flac/aac/ogg/m4a，最大 20MB</div>';
            toast(res?.message || '上传失败');
        }
    }

    // 前端获取音频时长（作为后备方案）
    function tryGetDurationFromBrowser(file, infoEl) {
        const url = URL.createObjectURL(file);
        const audio = new Audio();
        audio.preload = 'metadata';
        audio.onloadedmetadata = () => {
            if (audio.duration && isFinite(audio.duration)) {
                const dur = Math.round(audio.duration);
                _uploadedAudio.duration = dur;
                const m = Math.floor(dur / 60);
                const s = String(dur % 60).padStart(2, '0');
                infoEl.textContent = '时长: ' + m + ':' + s + '（自动获取）';
            }
            URL.revokeObjectURL(url);
        };
        audio.onerror = () => URL.revokeObjectURL(url);
        audio.src = url;
    }

    async function handleCoverUpload(file) {
        const allowExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        const ext = file.name.split('.').pop().toLowerCase();
        if (!allowExts.includes(ext)) {
            toast('仅支持 jpg/png/gif/webp 格式');
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            toast('封面图片不能超过 2MB');
            return;
        }

        const content = document.getElementById('coverUploadContent');
        content.innerHTML = '<div class="upload-icon">⏳</div><div class="upload-text">上传中...</div>';

        const fd = new FormData();
        fd.append('cover', file);

        const res = await upload('/music/upload-cover', fd);
        if (res && res.code === 200) {
            _uploadedCover = { url: res.data.url };
            content.innerHTML = `<img src="${res.data.url}" class="upload-preview-img" alt="封面"><div class="upload-hint">点击可重新上传</div>`;
            toast('封面上传成功');
        } else {
            content.innerHTML = '<div class="upload-icon">🖼️</div><div class="upload-text">上传失败，点击重试</div><div class="upload-hint">支持 jpg/png/gif/webp，最大 2MB</div>';
            toast(res?.message || '上传失败');
        }
    }

    window.saveMusic = async function() {
        const id = document.getElementById('musicId').value;
        const title = document.getElementById('musicTitle').value.trim();
        const artist = document.getElementById('musicArtist').value.trim();

        if (!title || !artist) {
            toast('请填写标题和歌手');
            return;
        }

        if (!_uploadedAudio.url) {
            toast('请上传音频文件');
            return;
        }

        const data = {
            title,
            artist,
            album: document.getElementById('musicAlbum').value,
            category_id: document.getElementById('musicCategory').value,
            audio_url: _uploadedAudio.url,
            cover_url: _uploadedCover.url,
            duration: _uploadedAudio.duration || 0
        };

        const res = id ? await post(`/music/update/${id}`, data) : await post('/music/create', data);
        if (res.code === 200) {
            toast(res.message);
            modal.hide();
            loadPage('music');
        } else {
            toast(res.message || '操作失败');
        }
    };

    window.editMusic = async function(id) {
        const res = await get('/music/detail', { id });
        if (res.code === 200) showMusicForm(res.data);
    };

    window.toggleMusicStatus = async function(id, status) {
        const res = await put(`/music/status/${id}`, { status });
        toast(res.message || '操作成功');
        await loadMusicPage(_state.music.page);
    };

    window.searchMusic = async function() {
        const keyword = document.getElementById('musicSearch')?.value || '';
        _state.music.keyword = keyword;
        _state.music.page = 1;
        await loadMusicPage(1);
    };

    window.loadMusicPage = async function(page) {
        _state.music.page = page;
        const table = document.getElementById('musicTable');
        if (table) table.innerHTML = '<tr><td colspan="7"><div class="table-loading"><div class="table-loading-spinner"></div><span>加载中...</span></div></td></tr>';
        const res = await get('/music/list', { keyword: _state.music.keyword, page, limit: 20 });
        const list = res.data?.data || res.data || [];
        const total = res.data?.total || list.length;
        const totalPages = Math.ceil(total / 20) || 1;
        if (table) table.innerHTML = renderMusicRows(list);
        const pag = document.getElementById('musicPagination');
        if (pag) pag.innerHTML = renderPagination(page, totalPages, 'loadMusicPage', total);
    };

    window.searchUser = async function() {
        const keyword = document.getElementById('userSearch')?.value || '';
        _state.user.keyword = keyword;
        _state.user.page = 1;
        await loadUserPage(1);
    };

    window.loadUserPage = async function(page) {
        _state.user.page = page;
        const table = document.getElementById('userTable');
        if (table) table.innerHTML = '<tr><td colspan="6"><div class="table-loading"><div class="table-loading-spinner"></div><span>加载中...</span></div></td></tr>';
        const res = await get('/user/list', { keyword: _state.user.keyword, page, limit: 20 });
        const list = res.data?.data || res.data || [];
        const total = res.data?.total || list.length;
        const totalPages = Math.ceil(total / 20) || 1;
        if (table) table.innerHTML = renderUserRows(list);
        const pag = document.getElementById('userPagination');
        if (pag) pag.innerHTML = renderPagination(page, totalPages, 'loadUserPage', total);
    };

    window.loadLogPage = async function(page) {
        _state.log.page = page;
        const table = document.getElementById('logTable');
        if (table) table.innerHTML = '<tr><td colspan="6"><div class="table-loading"><div class="table-loading-spinner"></div><span>加载中...</span></div></td></tr>';
        const res = await get('/log/list', { page, limit: 20 });
        const list = res.data?.data || res.data || [];
        const total = res.data?.total || list.length;
        const totalPages = Math.ceil(total / 20) || 1;
        if (table) table.innerHTML = renderLogRows(list);
        const pag = document.getElementById('logPagination');
        if (pag) pag.innerHTML = renderPagination(page, totalPages, 'loadLogPage', total);
    };

    // 分类操作
    window.showCategoryForm = function(cat = null) {
        modal.show(cat ? '编辑分类' : '新增分类', `
            <form id="categoryForm">
                <input type="hidden" id="catId" value="${cat?.id || ''}">
                <div class="form-group">
                    <label class="form-label">名称 *</label>
                    <input type="text" class="form-input" id="catName" value="${cat?.name || ''}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">排序</label>
                    <input type="number" class="form-input" id="catSort" value="${cat?.sort_order || 0}">
                </div>
                <div class="form-group">
                    <label class="form-label">状态</label>
                    <select class="form-select" id="catStatus">
                        <option value="1" ${cat?.status === 1 ? 'selected' : ''}>启用</option>
                        <option value="0" ${cat?.status === 0 ? 'selected' : ''}>禁用</option>
                    </select>
                </div>
            </form>
        `, saveCategory);
    };

    window.saveCategory = async function() {
        const id = document.getElementById('catId').value;
        const data = {
            name: document.getElementById('catName').value,
            sort_order: parseInt(document.getElementById('catSort').value) || 0,
            status: parseInt(document.getElementById('catStatus').value)
        };

        const res = id ? await put(`/category/update/${id}`, data) : await post('/category/create', data);
        if (res.code === 200) {
            toast(res.message);
            modal.hide();
            loadPage('category');
        } else {
            toast(res.message || '操作失败');
        }
    };

    window.editCategory = function(id, name, sort, status) {
        showCategoryForm({ id, name, sort_order: sort, status });
    };

    window.deleteCategory = async function(id) {
        const confirmed = await confirmDialog.show('删除后将无法恢复，确定要删除该分类吗？');
        if (!confirmed) return;
        const res = await del(`/category/delete/${id}`);
        toast(res.message || '删除成功');
        loadPage('category');
    };

    /**
     * 渲染完整分页组件（返回 HTML 字符串）
     * @param {number} currentPg   当前页
     * @param {number} totalPages  总页数
     * @param {string} handler     全局函数名，handler(page) 形式
     * @param {number} [total]     总记录数（可选）
     */
    function renderPagination(currentPg, totalPages, handler, total) {
        if (totalPages <= 0) return '';

        const totalStr = total !== undefined
            ? `<span class="pag-total">共 <strong>${total}</strong> 条记录</span>` : '';

        const fd = currentPg <= 1 ? 'disabled' : '';
        const ld = currentPg >= totalPages ? 'disabled' : '';
        const fo = currentPg > 1 ? `onclick="${handler}(1)"` : '';
        const po = currentPg > 1 ? `onclick="${handler}(${currentPg - 1})"` : '';
        const no = currentPg < totalPages ? `onclick="${handler}(${currentPg + 1})"` : '';
        const lo = currentPg < totalPages ? `onclick="${handler}(${totalPages})"` : '';

        // 页码
        let nums = '';
                  const addBtn = (p) => {
                      nums += `<button class="pag-btn${p === currentPg ? ' active' : ''}" onclick="${handler}(${p})" style="display:inline-flex;align-items:center;justify-content:center;">${p}</button>`;
                  };
                  const addDot = () => { nums += `<span class="pag-ellipsis" style="padding:0 2px;color:#999;">···</span>`; };

        if (totalPages <= 7) {
            for (let i = 1; i <= totalPages; i++) addBtn(i);
        } else {
            addBtn(1);
            if (currentPg > 4) addDot();
            const s = Math.max(2, currentPg - 2);
            const e = Math.min(totalPages - 1, currentPg + 2);
            for (let i = s; i <= e; i++) addBtn(i);
            if (currentPg < totalPages - 3) addDot();
            addBtn(totalPages);
        }

        const pageInfo = `<span class="pag-page-info">第 <strong>${currentPg}</strong>/<strong>${totalPages}</strong> 页</span>`;
        const gotoInput = `<span class="pag-goto">跳至<input class="pag-goto-input" type="number" min="1" max="${totalPages}" placeholder="${currentPg}" onkeydown="if(event.key==='Enter'){var p=parseInt(this.value);if(p>=1&&p<=${totalPages}){${handler}(p);this.value='';}}" >页</span>`;

        return `
        <div class="pagination-wrap" style="display:flex;align-items:center;flex-wrap:wrap;gap:8px;justify-content:center;padding:12px 0;">
            ${totalStr}
            <div class="pag-nav-group" style="display:flex;align-items:center;gap:4px;">
                <button class="pag-btn pag-nav" title="首页" ${fd} ${fo} style="display:inline-flex;align-items:center;justify-content:center;">|&#9664;</button>
                <button class="pag-btn pag-nav" title="上一页" ${fd} ${po} style="display:inline-flex;align-items:center;justify-content:center;">&#9664;</button>
                <div class="pag-pages" style="display:flex;align-items:center;gap:4px;">${nums}</div>
                <button class="pag-btn pag-nav" title="下一页" ${ld} ${no} style="display:inline-flex;align-items:center;justify-content:center;">&#9654;</button>
                <button class="pag-btn pag-nav" title="末页" ${ld} ${lo} style="display:inline-flex;align-items:center;justify-content:center;">&#9654;|</button>
            </div>
            ${pageInfo}
            ${gotoInput}
        </div>`;
    }

    function renderCategoryRows(list) {
        if (!list || !list.length) return emptyRow(5);
        return list.map(c => `
            <tr>
                <td>${c.id}</td>
                <td>${c.name}</td>
                <td>${c.sort_order}</td>
                <td><span class="status ${c.status === 1 ? 'status-success' : 'status-danger'}">${c.status === 1 ? '启用' : '禁用'}</span></td>
                <td>
                    <button class="btn btn-sm btn-outline" onclick="editCategory(${c.id}, '${c.name}', ${c.sort_order}, ${c.status})">编辑</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteCategory(${c.id})">删除</button>
                </td>
            </tr>
        `).join('');
    }

    // 分类搜索
    window.searchCategory = async function() {
        const keyword = document.getElementById('categorySearch')?.value || '';
        const res = await get('/category/list', { page: 1, limit: 20, keyword });
        const list = res.data?.data || res.data || [];
        const total = res.data?.total || list.length;
        const totalPages = Math.ceil(total / 20);
        const table = document.getElementById('categoryTable');
        if (table) {
            table.innerHTML = renderCategoryRows(list);
            const pag = document.getElementById('categoryPagination');
            if (pag) pag.innerHTML = renderPagination(1, totalPages, 'loadCategoryPage', total);
        }
    };

    // 分类分页跳转
    window.loadCategoryPage = async function(page) {
        const keyword = document.getElementById('categorySearch')?.value || '';
        const res = await get('/category/list', { page, limit: 20, keyword });
        const list = res.data?.data || res.data || [];
        const total = res.data?.total || list.length;
        const totalPages = Math.ceil(total / 20);
        const table = document.getElementById('categoryTable');
        if (table) {
            table.innerHTML = renderCategoryRows(list);
            const pag = document.getElementById('categoryPagination');
            if (pag) pag.innerHTML = renderPagination(page, totalPages, 'loadCategoryPage', total);
        }
    };

    // 用户操作
    window.toggleUserStatus = async function(id, status) {
        const res = await put(`/user/status/${id}`, { status });
        toast(res.message || '操作成功');
        await loadUserPage(_state.user.page);
    };

    // 页面加载（最小300ms loading效果）
    async function loadPage(page) {
        currentPage = page;
        document.querySelectorAll('.nav-item').forEach(el => {
            el.classList.toggle('active', el.dataset.page === page);
        });
        
        const content = document.getElementById('content');
        content.innerHTML = '<div class="table-loading"><div class="table-loading-spinner"></div><span>加载中...</span></div>';
        
        if (pages[page]) {
            const startTime = Date.now();
            const html = await pages[page]();
            const elapsed = Date.now() - startTime;
            if (elapsed < 300) {
                await new Promise(r => setTimeout(r, 300 - elapsed));
            }
            content.innerHTML = html;
        }
    }

    // 初始化
    function init() {
        // 检查登录
        if (!localStorage.getItem('admin_token')) {
            window.location.href = '/login';
            return;
        }

        // 显示管理员名称
        const admin = JSON.parse(localStorage.getItem('admin_info') || '{}');
        document.getElementById('adminName').textContent = admin.nickname || admin.username || '管理员';

        // 导航点击
        document.querySelectorAll('.nav-item').forEach(el => {
            el.addEventListener('click', (e) => {
                e.preventDefault();
                loadPage(el.dataset.page);
            });
        });

        // 退出登录
        document.getElementById('logoutBtn').addEventListener('click', async () => {
            await post('/auth/logout', {});
            localStorage.removeItem('admin_token');
            localStorage.removeItem('admin_info');
            window.location.href = '/login';
        });

        // 移动端菜单
        document.getElementById('menuToggle').addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // 模态框
        document.getElementById('modalClose').addEventListener('click', () => modal.hide());
        document.getElementById('modalCancel').addEventListener('click', () => modal.hide());
        document.getElementById('modalConfirm').addEventListener('click', () => {
            if (modal.onConfirm) modal.onConfirm();
        });

        // 加载默认页面
        loadPage('dashboard');
    }

    init();
})();
