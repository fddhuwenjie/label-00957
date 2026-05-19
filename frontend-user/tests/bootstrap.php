<?php

require __DIR__ . '/../vendor/autoload.php';

define('APP_PATH', __DIR__ . '/../app/');
define('RUNTIME_PATH', __DIR__ . '/../runtime/');
define('PUBLIC_PATH', __DIR__ . '/../public/');
define('ROOT_PATH', __DIR__ . '/../');

if (file_exists(__DIR__ . '/../app/common.php')) {
    require __DIR__ . '/../app/common.php';
}

if (file_exists(__DIR__ . '/../vendor/topthink/framework/src/helper.php')) {
    require __DIR__ . '/../vendor/topthink/framework/src/helper.php';
}
