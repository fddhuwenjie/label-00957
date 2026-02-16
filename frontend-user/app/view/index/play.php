{extend name="layout/base" /}

{block name="css"}
<link rel="stylesheet" href="/asset/css?file=pages/play">
{/block}

{block name="content"}
<div class="page-play" data-music-id="{$musicId}">
    <div class="play-bg" id="playBg"></div>
    <div class="play-container">
        <!-- 封面区域 -->
        <div class="play-cover-area">
            <div class="play-disc">
                <div class="disc-wrapper" id="discWrapper">
                    <img src="/assets/images/cover-default.svg" alt="封面" class="disc-cover" id="discCover">
                </div>
            </div>
        </div>

        <!-- 信息区域 -->
        <div class="play-info-area">
            <h1 class="play-title" id="playTitle">加载中...</h1>
            <p class="play-artist" id="playArtist">-</p>
            <p class="play-album" id="playAlbum">-</p>

            <!-- 歌词 -->
            <div class="lyrics-container" id="lyricsContainer">
                <div class="lyrics-content" id="lyricsContent">
                    <p class="lyrics-line">暂无歌词</p>
                </div>
            </div>

            <!-- 操作按钮 -->
            <div class="play-actions">
                <button class="action-btn" id="actionFavorite">
                    <svg viewBox="0 0 24 24"><path d="M16.5 3c-1.74 0-3.41.81-4.5 2.09C10.91 3.81 9.24 3 7.5 3 4.42 3 2 5.42 2 8.5c0 3.78 3.4 6.86 8.55 11.54L12 21.35l1.45-1.32C18.6 15.36 22 12.28 22 8.5 22 5.42 19.58 3 16.5 3z"/></svg>
                    <span>收藏</span>
                </button>
                <button class="action-btn" id="actionShare">
                    <svg viewBox="0 0 24 24"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92-1.31-2.92-2.92-2.92z"/></svg>
                    <span>分享</span>
                </button>
                <button class="action-btn" id="actionDownload">
                    <svg viewBox="0 0 24 24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/></svg>
                    <span>下载</span>
                </button>
            </div>
        </div>
    </div>
</div>
{/block}

{block name="js"}
<script src="/asset/js?file=pages/play"></script>
{/block}
