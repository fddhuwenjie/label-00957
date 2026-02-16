-- 音乐发行平台数据库初始化脚本 (PostgreSQL)

-- ----------------------------
-- 用户表
-- ----------------------------
DROP TABLE IF EXISTS users CASCADE;
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nickname VARCHAR(50),
    avatar VARCHAR(255),
    status SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP
);
COMMENT ON TABLE users IS '用户表';
COMMENT ON COLUMN users.email IS '邮箱';
COMMENT ON COLUMN users.password IS '密码';
COMMENT ON COLUMN users.nickname IS '昵称';
COMMENT ON COLUMN users.avatar IS '头像';
COMMENT ON COLUMN users.status IS '状态:0禁用,1正常';

-- ----------------------------
-- 管理员表
-- ----------------------------
DROP TABLE IF EXISTS admins CASCADE;
CREATE TABLE admins (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nickname VARCHAR(50),
    status SMALLINT NOT NULL DEFAULT 1,
    last_login TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
COMMENT ON TABLE admins IS '管理员表';
COMMENT ON COLUMN admins.username IS '用户名';
COMMENT ON COLUMN admins.password IS '密码';
COMMENT ON COLUMN admins.nickname IS '昵称';
COMMENT ON COLUMN admins.status IS '状态:0禁用,1正常';
COMMENT ON COLUMN admins.last_login IS '最后登录时间';

-- ----------------------------
-- 分类表
-- ----------------------------
DROP TABLE IF EXISTS categories CASCADE;
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    icon VARCHAR(50),
    sort_order INT NOT NULL DEFAULT 0,
    status SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
COMMENT ON TABLE categories IS '分类表';
COMMENT ON COLUMN categories.name IS '分类名称';
COMMENT ON COLUMN categories.icon IS '图标';
COMMENT ON COLUMN categories.sort_order IS '排序';
COMMENT ON COLUMN categories.status IS '状态:0禁用,1正常';

-- ----------------------------
-- 音乐表
-- ----------------------------
DROP TABLE IF EXISTS music CASCADE;
CREATE TABLE music (
    id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    artist VARCHAR(100) NOT NULL,
    album VARCHAR(200),
    category_id INT,
    cover_url VARCHAR(255),
    audio_url VARCHAR(255) NOT NULL,
    lyrics TEXT,
    duration INT NOT NULL DEFAULT 0,
    play_count INT NOT NULL DEFAULT 0,
    status SMALLINT NOT NULL DEFAULT 1,
    user_id INT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_music_category ON music(category_id);
CREATE INDEX idx_music_user ON music(user_id);
CREATE INDEX idx_music_play_count ON music(play_count);
COMMENT ON TABLE music IS '音乐表';
COMMENT ON COLUMN music.title IS '标题';
COMMENT ON COLUMN music.artist IS '艺术家';
COMMENT ON COLUMN music.album IS '专辑';
COMMENT ON COLUMN music.category_id IS '分类ID';
COMMENT ON COLUMN music.cover_url IS '封面URL';
COMMENT ON COLUMN music.audio_url IS '音频URL';
COMMENT ON COLUMN music.lyrics IS '歌词';
COMMENT ON COLUMN music.duration IS '时长(秒)';
COMMENT ON COLUMN music.play_count IS '播放次数';
COMMENT ON COLUMN music.status IS '状态:0下架,1上架';
COMMENT ON COLUMN music.user_id IS '上传用户ID';

-- ----------------------------
-- 收藏表
-- ----------------------------
DROP TABLE IF EXISTS favorites CASCADE;
CREATE TABLE favorites (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    music_id INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, music_id)
);
CREATE INDEX idx_favorites_user ON favorites(user_id);
COMMENT ON TABLE favorites IS '收藏表';
COMMENT ON COLUMN favorites.user_id IS '用户ID';
COMMENT ON COLUMN favorites.music_id IS '音乐ID';

-- ----------------------------
-- 播放历史表
-- ----------------------------
DROP TABLE IF EXISTS play_history CASCADE;
CREATE TABLE play_history (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    music_id INT NOT NULL,
    played_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_play_history_user ON play_history(user_id);
CREATE INDEX idx_play_history_played_at ON play_history(played_at);
COMMENT ON TABLE play_history IS '播放历史表';
COMMENT ON COLUMN play_history.user_id IS '用户ID';
COMMENT ON COLUMN play_history.music_id IS '音乐ID';

-- ----------------------------
-- 操作日志表（管理员）
-- ----------------------------
DROP TABLE IF EXISTS operation_logs CASCADE;
CREATE TABLE operation_logs (
    id SERIAL PRIMARY KEY,
    admin_id INT NOT NULL,
    module VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    content TEXT,
    ip VARCHAR(50),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_operation_logs_admin ON operation_logs(admin_id);
CREATE INDEX idx_operation_logs_created_at ON operation_logs(created_at);
COMMENT ON TABLE operation_logs IS '操作日志表';
COMMENT ON COLUMN operation_logs.admin_id IS '管理员ID';
COMMENT ON COLUMN operation_logs.module IS '模块';
COMMENT ON COLUMN operation_logs.action IS '操作';
COMMENT ON COLUMN operation_logs.content IS '内容';
COMMENT ON COLUMN operation_logs.ip IS 'IP地址';

-- ----------------------------
-- 用户日志表
-- ----------------------------
DROP TABLE IF EXISTS user_log CASCADE;
CREATE TABLE user_log (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    content TEXT,
    ip VARCHAR(50),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_user_log_user ON user_log(user_id);
CREATE INDEX idx_user_log_created_at ON user_log(created_at);
COMMENT ON TABLE user_log IS '用户日志表';
COMMENT ON COLUMN user_log.user_id IS '用户ID';
COMMENT ON COLUMN user_log.action IS '操作';
COMMENT ON COLUMN user_log.content IS '内容';
COMMENT ON COLUMN user_log.ip IS 'IP地址';

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
