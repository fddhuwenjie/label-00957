<?php
// PHP 内置服务器路由脚本（ThinkPHP 开发环境专用）
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// 静态文件直接返回，由 PHP 内置服务器处理
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// 其余请求全部交由 index.php 处理
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/index.php';
require __DIR__ . '/index.php';
