-- 音乐发行平台数据库初始化脚本 (SQLite)

-- ----------------------------
-- 用户表
-- ----------------------------
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nickname VARCHAR(50),
    avatar VARCHAR(255),
    status INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME
);

-- ----------------------------
-- 管理员表
-- ----------------------------
DROP TABLE IF EXISTS admins;
CREATE TABLE admins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nickname VARCHAR(50),
    status INTEGER NOT NULL DEFAULT 1,
    last_login DATETIME,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ----------------------------
-- 分类表
-- ----------------------------
DROP TABLE IF EXISTS categories;
CREATE TABLE categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) NOT NULL,
    icon VARCHAR(50),
    sort_order INTEGER NOT NULL DEFAULT 0,
    status INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ----------------------------
-- 音乐表
-- ----------------------------
DROP TABLE IF EXISTS music;
CREATE TABLE music (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(200) NOT NULL,
    artist VARCHAR(100) NOT NULL,
    album VARCHAR(200),
    category_id INTEGER,
    cover_url VARCHAR(255),
    audio_url VARCHAR(255) NOT NULL,
    lyrics TEXT,
    duration INTEGER NOT NULL DEFAULT 0,
    play_count INTEGER NOT NULL DEFAULT 0,
    status INTEGER NOT NULL DEFAULT 1,
    user_id INTEGER,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_music_category ON music(category_id);
CREATE INDEX idx_music_user ON music(user_id);
CREATE INDEX idx_music_play_count ON music(play_count);

-- ----------------------------
-- 收藏表
-- ----------------------------
DROP TABLE IF EXISTS favorites;
CREATE TABLE favorites (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    music_id INTEGER NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, music_id)
);
CREATE INDEX idx_favorites_user ON favorites(user_id);

-- ----------------------------
-- 播放历史表
-- ----------------------------
DROP TABLE IF EXISTS play_history;
CREATE TABLE play_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    music_id INTEGER NOT NULL,
    played_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_play_history_user ON play_history(user_id);
CREATE INDEX idx_play_history_played_at ON play_history(played_at);

-- ----------------------------
-- 操作日志表（管理员）
-- ----------------------------
DROP TABLE IF EXISTS operation_logs;
CREATE TABLE operation_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    admin_id INTEGER NOT NULL,
    module VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    content TEXT,
    ip VARCHAR(50),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_operation_logs_admin ON operation_logs(admin_id);
CREATE INDEX idx_operation_logs_created_at ON operation_logs(created_at);

-- ----------------------------
-- 用户日志表
-- ----------------------------
DROP TABLE IF EXISTS user_log;
CREATE TABLE user_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    action VARCHAR(50) NOT NULL,
    content TEXT,
    ip VARCHAR(50),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_user_log_user ON user_log(user_id);
CREATE INDEX idx_user_log_created_at ON user_log(created_at);

-- ----------------------------
-- 初始数据
-- ----------------------------

-- 管理员 (密码: admin123)
INSERT INTO admins (username, password, nickname) VALUES
('admin', '$2y$10$xrnGd9tCaH6dY.Iylbw2sumQZrG7Fvo1tv39C8yY6kghl6zvxO0F.', '超级管理员');

-- 测试用户 (密码: admin123)
INSERT INTO users (email, password, nickname) VALUES
('user@test.com', '$2y$10$xrnGd9tCaH6dY.Iylbw2sumQZrG7Fvo1tv39C8yY6kghl6zvxO0F.', '测试用户');

-- 分类
INSERT INTO categories (name, icon, sort_order) VALUES
('流行', 'icon-pop', 1),
('摇滚', 'icon-rock', 2),
('民谣', 'icon-folk', 3),
('电子', 'icon-electronic', 4),
('古典', 'icon-classical', 5),
('嘻哈', 'icon-hiphop', 6);

-- 示例音乐数据（使用公开的示例音频）
INSERT INTO music (title, artist, album, category_id, cover_url, audio_url, duration, play_count) VALUES
('夜曲', '周杰伦', '十一月的肖邦', 1, '/assets/images/cover-default.svg', '/assets/audio/sample1.mp3', 269, 1520),
('光年之外', '邓紫棋', '光年之外', 1, '/assets/images/cover-default.svg', '/assets/audio/sample2.mp3', 235, 2340),
('平凡之路', '朴树', '猎户星座', 3, '/assets/images/cover-default.svg', '/assets/audio/sample3.mp3', 302, 1890),
('海阔天空', 'Beyond', '乐与怒', 2, '/assets/images/cover-default.svg', '/assets/audio/sample4.mp3', 326, 3210),
('成都', '赵雷', '无法长大', 3, '/assets/images/cover-default.svg', '/assets/audio/sample5.mp3', 328, 2780),
('Faded', 'Alan Walker', 'Different World', 4, '/assets/images/cover-default.svg', '/assets/audio/sample6.mp3', 212, 4520);
