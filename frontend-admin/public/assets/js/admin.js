/**
 * 管理后台主脚本
 */
(function() {
    'use strict';

    const API_BASE = '/api';
    let currentPage = 'dashboard';

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
                            <tbody>${(recent.data || []).map(m => `<tr><td>${m.title}</td><td>${m.artist}</td></tr>`).join('')}</tbody>
                        </table>
                    </div>
                    <div class="card">
                        <div class="card-header"><span class="card-title">热门音乐</span></div>
                        <table class="table">
                            <thead><tr><th>标题</th><th>播放量</th></tr></thead>
                            <tbody>${(top.data || []).map(m => `<tr><td>${m.title}</td><td>${formatNumber(m.play_count)}</td></tr>`).join('')}</tbody>
                        </table>
                    </div>
                </div>
            `;
        },

        async music() {
            const res = await get('/music/list', { page: 1, limit: 20 });
            const cats = await get('/category/list');
            const list = res.data?.data || res.data || [];
            const categories = cats.data || [];

            window._categories = categories;
            window._musicList = list;

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
                </div>
            `;
        },

        async category() {
            const res = await get('/category/list');
            const list = res.data || [];

            return `
                <h2 style="margin-bottom:20px">分类管理</h2>
                <div class="toolbar">
                    <button class="btn btn-primary" onclick="showCategoryForm()">+ 新增分类</button>
                </div>
                <div class="card">
                    <table class="table">
                        <thead><tr><th>ID</th><th>名称</th><th>排序</th><th>状态</th><th>操作</th></tr></thead>
                        <tbody id="categoryTable">${list.map(c => `
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
                        `).join('')}</tbody>
                    </table>
                </div>
            `;
        },

        async user() {
            const res = await get('/user/list', { page: 1, limit: 20 });
            const list = res.data?.data || res.data || [];

            return `
                <h2 style="margin-bottom:20px">用户管理</h2>
                <div class="card">
                    <table class="table">
                        <thead><tr><th>ID</th><th>邮箱</th><th>昵称</th><th>状态</th><th>注册时间</th><th>操作</th></tr></thead>
                        <tbody>${list.map(u => `
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
                        `).join('')}</tbody>
                    </table>
                </div>
            `;
        },

        async log() {
            const res = await get('/log/list', { page: 1, limit: 50 });
            const list = res.data?.data || res.data || [];

            return `
                <h2 style="margin-bottom:20px">操作日志</h2>
                <div class="card">
                    <table class="table">
                        <thead><tr><th>时间</th><th>管理员</th><th>模块</th><th>操作</th><th>内容</th><th>IP</th></tr></thead>
                        <tbody>${list.map(l => `
                            <tr>
                                <td>${l.created_at}</td>
                                <td>${l.admin?.nickname || l.admin?.username || '-'}</td>
                                <td>${l.module}</td>
                                <td>${l.action}</td>
                                <td>${l.content || '-'}</td>
                                <td>${l.ip || '-'}</td>
                            </tr>
                        `).join('')}</tbody>
                    </table>
                </div>
            `;
        }
    };

    // 辅助函数
    function formatNumber(n) {
        return n >= 10000 ? (n / 10000).toFixed(1) + '万' : n;
    }

    function renderMusicRows(list) {
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
        loadPage('music');
    };

    window.searchMusic = async function() {
        const keyword = document.getElementById('musicSearch').value;
        const table = document.getElementById('musicTable');
        table.innerHTML = '<tr><td colspan="7"><div class="table-loading"><div class="table-loading-spinner"></div><span>搜索中...</span></div></td></tr>';
        const startTime = Date.now();
        const res = await get('/music/list', { keyword, page: 1, limit: 20 });
        const elapsed = Date.now() - startTime;
        if (elapsed < 300) {
            await new Promise(r => setTimeout(r, 300 - elapsed));
        }
        const list = res.data?.data || res.data || [];
        table.innerHTML = renderMusicRows(list);
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

    // 用户操作
    window.toggleUserStatus = async function(id, status) {
        const res = await put(`/user/status/${id}`, { status });
        toast(res.message || '操作成功');
        loadPage('user');
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
