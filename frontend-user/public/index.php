<?php
/**
 * 音乐发行平台 - 用户端入口文件
 * 多端合一响应式设计
 */

namespace think;

require __DIR__ . '/../vendor/autoload.php';

// 执行HTTP应用并响应
$http = (new App())->http;
$response = $http->run();
$response->send();
$http->end($response);
