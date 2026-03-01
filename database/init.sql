-- 音乐发行平台数据库初始化脚本 (MySQL - 默认)
-- 其他数据库类型请使用对应的初始化脚本:
--   MySQL:      init_mysql.sql
--   PostgreSQL: init_pgsql.sql
--   SQLite:     init_sqlite.sql
-- 字符集: utf8mb4

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- 用户表
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `email` varchar(100) NOT NULL COMMENT '邮箱',
    `password` varchar(255) NOT NULL COMMENT '密码',
    `nickname` varchar(50) DEFAULT NULL COMMENT '昵称',
    `avatar` varchar(255) DEFAULT NULL COMMENT '头像',
    `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态:0禁用,1正常',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';

-- ----------------------------
-- 管理员表
-- ----------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL COMMENT '用户名',
    `password` varchar(255) NOT NULL COMMENT '密码',
    `nickname` varchar(50) DEFAULT NULL COMMENT '昵称',
    `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态:0禁用,1正常',
    `last_login` datetime DEFAULT NULL COMMENT '最后登录时间',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员表';

-- ----------------------------
-- 分类表
-- ----------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL COMMENT '分类名称',
    `icon` varchar(50) DEFAULT NULL COMMENT '图标',
    `sort_order` int NOT NULL DEFAULT 0 COMMENT '排序',
    `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态:0禁用,1正常',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='分类表';

-- ----------------------------
-- 音乐表
-- ----------------------------
DROP TABLE IF EXISTS `music`;
CREATE TABLE `music` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(200) NOT NULL COMMENT '标题',
    `artist` varchar(100) NOT NULL COMMENT '艺术家',
    `album` varchar(200) DEFAULT NULL COMMENT '专辑',
    `category_id` int unsigned DEFAULT NULL COMMENT '分类ID',
    `cover_url` varchar(255) DEFAULT NULL COMMENT '封面URL',
    `audio_url` varchar(255) NOT NULL COMMENT '音频URL',
    `lyrics` text COMMENT '歌词',
    `duration` int NOT NULL DEFAULT 0 COMMENT '时长(秒)',
    `play_count` int NOT NULL DEFAULT 0 COMMENT '播放次数',
    `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态:0下架,1上架',
    `user_id` int unsigned DEFAULT NULL COMMENT '上传用户ID',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_category` (`category_id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_play_count` (`play_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='音乐表';

-- ----------------------------
-- 收藏表
-- ----------------------------
DROP TABLE IF EXISTS `favorites`;
CREATE TABLE `favorites` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int unsigned NOT NULL COMMENT '用户ID',
    `music_id` int unsigned NOT NULL COMMENT '音乐ID',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_music` (`user_id`, `music_id`),
    KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='收藏表';

-- ----------------------------
-- 播放历史表
-- ----------------------------
DROP TABLE IF EXISTS `play_history`;
CREATE TABLE `play_history` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int unsigned NOT NULL COMMENT '用户ID',
    `music_id` int unsigned NOT NULL COMMENT '音乐ID',
    `played_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_played_at` (`played_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='播放历史表';

-- ----------------------------
-- 操作日志表（管理员）
-- ----------------------------
DROP TABLE IF EXISTS `operation_logs`;
CREATE TABLE `operation_logs` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `admin_id` int unsigned NOT NULL COMMENT '管理员ID',
    `module` varchar(50) NOT NULL COMMENT '模块',
    `action` varchar(50) NOT NULL COMMENT '操作',
    `content` text COMMENT '内容',
    `ip` varchar(50) DEFAULT NULL COMMENT 'IP地址',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_admin` (`admin_id`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='操作日志表';

-- ----------------------------
-- 用户日志表
-- ----------------------------
DROP TABLE IF EXISTS `user_log`;
CREATE TABLE `user_log` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int unsigned NOT NULL COMMENT '用户ID',
    `action` varchar(50) NOT NULL COMMENT '操作',
    `content` text COMMENT '内容',
    `ip` varchar(50) DEFAULT NULL COMMENT 'IP地址',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户日志表';

-- ----------------------------
-- 初始数据
-- ----------------------------

-- 管理员 (密码: admin123)
INSERT INTO `admins` (`username`, `password`, `nickname`) VALUES
('admin', '$2y$10$xrnGd9tCaH6dY.Iylbw2sumQZrG7Fvo1tv39C8yY6kghl6zvxO0F.', '超级管理员');

-- 测试用户 (密码: admin123)
INSERT INTO `users` (`email`, `password`, `nickname`) VALUES
('user@test.com', '$2y$10$xrnGd9tCaH6dY.Iylbw2sumQZrG7Fvo1tv39C8yY6kghl6zvxO0F.', '测试用户');

-- 分类
INSERT INTO `categories` (`name`, `icon`, `sort_order`) VALUES
('流行', 'icon-pop', 1),
('摇滚', 'icon-rock', 2),
('民谣', 'icon-folk', 3),
('电子', 'icon-electronic', 4),
('古典', 'icon-classical', 5),
('嘻哈', 'icon-hiphop', 6);

-- 示例音乐数据（使用主题封面和示例音频）
INSERT INTO `music` (`title`, `artist`, `album`, `category_id`, `cover_url`, `audio_url`, `duration`, `play_count`) VALUES
('夜曲', '周杰伦', '十一月的肖邦', 1, '/assets/covers/nightcurve.svg', '/assets/audio/sample1.mp3', 269, 1520),
('光年之外', '邓紫棋', '光年之外', 1, '/assets/covers/lightyears.svg', '/assets/audio/sample2.mp3', 235, 2340),
('平凡之路', '朴树', '猎户星座', 3, '/assets/covers/ordinary-road.svg', '/assets/audio/sample3.mp3', 302, 1890),
('海阔天空', 'Beyond', '乐与怒', 2, '/assets/covers/boundless-sky.svg', '/assets/audio/sample4.mp3', 326, 3210),
('成都', '赵雷', '无法长大', 3, '/assets/covers/chengdu.svg', '/assets/audio/sample5.mp3', 328, 2780),
('Faded', 'Alan Walker', 'Different World', 4, '/assets/covers/faded.svg', '/assets/audio/sample6.mp3', 212, 4520);

SET FOREIGN_KEY_CHECKS = 1;
