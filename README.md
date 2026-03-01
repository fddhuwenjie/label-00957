# 多端合一响应式音乐发行平台

## How to Run

### Docker 一键部署（推荐）

```bash

# 1. 启动所有服务
docker-compose up -d --build

# 3. 访问地址
# 前端用户端: http://localhost:8081
# 后台管理端: http://localhost:8082
```

### 本地开发运行

数据库通过 Docker 启动，PHP 应用本地直接运行，无需将整个项目容器化。

**环境要求：**
- PHP >= 8.1，需开启扩展：`pdo_mysql`、`gd`、`zip`、`opcache`
- Composer 2
- Docker（仅用于启动 MySQL）

```bash
# 1. 用 Docker 启动 MySQL 数据库（映射到本机 3307 端口）
docker-compose up -d mysql
```

```bash
# 2. 配置并启动用户端（终端一）
cd frontend-user

# 复制环境配置，数据库连接指向本机 3307 端口
cp .env.example .env
# 编辑 .env：
# DB_HOST = 127.0.0.1
# DB_PORT = 3307
# DB_DATABASE = music_platform
# DB_USERNAME = root
# DB_PASSWORD = root123

composer install
php think run --port 8081
```

```bash
# 3. 配置并启动后台管理端（终端二）
cd frontend-admin

cp .env.example .env
# 编辑 .env（同上，DB_PORT = 3307）

composer install
php think run --port 8082
```

```
# 4. 访问地址
# 前端用户端: http://localhost:8081
# 后台管理端: http://localhost:8082
```

## Services

| 服务 | 端口 | 说明 |
|------|------|------|
| frontend-user | 8081 | 用户端（多端合一响应式） |
| frontend-admin | 8082 | 后台管理系统 |
| MySQL | 3307 | 数据库服务 |

## 测试账号

### 后台管理系统
- 管理员账号: `admin`
- 管理员密码: `admin123`

### 用户端
- 测试用户: `user@test.com`
- 测试密码: `admin123`

## 题目内容

使用ThinkPHP框架开发一套功能完善的多端合一响应式音乐发行平台网站。该平台需实现对PC端、手机端、平板端、微信公众号、微信小程序及H5等多终端的自适应浏览布局，确保在不同设备上均能提供良好的用户体验。 技术实现要求： 1. 采用响应式Web设计，使用CSS媒体查询、Flexbox或Grid布局实现多终端自适应 2. 实现动态资源加载机制，通过文件引入等动态写法保护页面源码，特别是动态加载CSS和JavaScript文件 3. 所有页面文件必须使用.php格式开发 4. 研究并应用多端合一网站的最佳实践，包括但不限于设备检测、视图适配和交互优化 安全与性能要求： 1. 确保动态资源加载机制能够有效防止用户直接查看核心页面源码 2. 优化资源加载性能，实现按需加载和资源缓存策略 3. 保证在各种网络环境下均有良好的加载速度和运行性能 兼容性要求： 1. 兼容主流现代浏览器（Chrome、Firefox、Safari、Edge最新版本） 2. 适配主流移动设备屏幕尺寸和分辨率 3. 确保在微信内置浏览器及小程序环境中正常运行 4.网络搜索多端合一写法 请在开发过程中遵循ThinkPHP框架的最佳实践，实现代码模块化、可维护性和可扩展性。

---

## 项目简介

本项目是一个基于 ThinkPHP 8 框架开发的多端合一响应式音乐发行平台，支持 PC端、手机端、平板端、微信公众号、微信小程序及 H5 等多终端自适应浏览。

### 核心特性

- **多端合一响应式设计**: 使用 CSS 媒体查询、Flexbox/Grid 布局实现全终端自适应
- **动态资源加载**: 通过 PHP 动态引入机制保护源码，实现按需加载
- **设备智能检测**: 自动识别访问设备类型，提供最优化的视图体验
- **微信生态兼容**: 完美支持微信内置浏览器和小程序 WebView

### 功能模块

1. **用户端**
   - 首页展示（轮播、推荐、排行榜）
   - 音乐浏览与搜索
   - 音乐播放器
   - 用户注册/登录
   - 个人中心

2. **后台管理**
   - 仪表盘数据统计
   - 音乐管理（CRUD）
   - 用户管理
   - 分类管理
   - 系统设置

### 技术架构

- **后端**: ThinkPHP 8 + MySQL 8.0
- **前端**: 原生 PHP 模板 + 响应式 CSS + Vanilla JS
- **容器化**: Docker + Docker Compose
