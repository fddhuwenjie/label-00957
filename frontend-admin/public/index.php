<?php
/**
 * 音乐发行平台 - 后台管理入口
 */

namespace think;

require __DIR__ . '/../vendor/autoload.php';

$http = (new App())->http;
$response = $http->run();
$response->send();
$http->end($response);
