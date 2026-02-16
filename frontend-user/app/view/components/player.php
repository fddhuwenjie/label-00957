<!-- 全局播放器 -->
<div class="player" id="player">
    <div class="player-inner container">
        <!-- 歌曲信息 -->
        <div class="player-info">
            <img src="/assets/images/cover-default.svg" alt="封面" class="player-cover" id="playerCover">
            <div class="player-meta">
                <div class="player-title" id="playerTitle">未播放</div>
                <div class="player-artist" id="playerArtist">-</div>
            </div>
        </div>

        <!-- 进度条 -->
        <div class="player-progress">
            <span class="player-time" id="playerCurrentTime">0:00</span>
            <div class="progress-bar" id="progressBar">
                <div class="progress-played" id="progressPlayed"></div>
                <div class="progress-handle" id="progressHandle"></div>
            </div>
            <span class="player-time" id="playerDuration">0:00</span>
        </div>

        <!-- 控制按钮 -->
        <div class="player-controls">
            <button class="player-btn" id="btnPrev" title="上一首">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 6h2v12H6zm3.5 6l8.5 6V6z"/></svg>
            </button>
            <button class="player-btn player-btn-play" id="btnPlay" title="播放">
                <svg class="icon-play" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                <svg class="icon-pause" viewBox="0 0 24 24" fill="currentColor" style="display:none"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
            </button>
            <button class="player-btn" id="btnNext" title="下一首">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z"/></svg>
            </button>
        </div>

        <!-- 附加控制 -->
        <div class="player-extra pc-only">
            <button class="player-btn" id="btnFavorite" title="收藏">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16.5 3c-1.74 0-3.41.81-4.5 2.09C10.91 3.81 9.24 3 7.5 3 4.42 3 2 5.42 2 8.5c0 3.78 3.4 6.86 8.55 11.54L12 21.35l1.45-1.32C18.6 15.36 22 12.28 22 8.5 22 5.42 19.58 3 16.5 3zm-4.4 15.55l-.1.1-.1-.1C7.14 14.24 4 11.39 4 8.5 4 6.5 5.5 5 7.5 5c1.54 0 3.04.99 3.57 2.36h1.87C13.46 5.99 14.96 5 16.5 5c2 0 3.5 1.5 3.5 3.5 0 2.89-3.14 5.74-7.9 10.05z"/></svg>
            </button>
            <div class="volume-control">
                <button class="player-btn" id="btnVolume" title="音量">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/></svg>
                </button>
                <div class="volume-slider">
                    <input type="range" min="0" max="100" value="80" id="volumeSlider">
                </div>
            </div>
            <button class="player-btn" id="btnPlaylist" title="播放列表">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M15 6H3v2h12V6zm0 4H3v2h12v-2zM3 16h8v-2H3v2zM17 6v8.18c-.31-.11-.65-.18-1-.18-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3V8h3V6h-5z"/></svg>
            </button>
        </div>
    </div>

    <!-- 音频元素 -->
    <audio id="audioElement" preload="metadata"></audio>
</div>

<!-- 播放列表弹窗 -->
<div class="playlist-modal" id="playlistModal">
    <div class="playlist-header">
        <h3>播放列表</h3>
        <button class="playlist-close" id="playlistClose">&times;</button>
    </div>
    <div class="playlist-content" id="playlistContent">
        <div class="playlist-empty">暂无播放列表</div>
    </div>
</div>
