<?php

require __DIR__ . '/../vendor/autoload.php';

define('APP_PATH', __DIR__ . '/../app/');
define('ROOT_PATH', __DIR__ . '/../');
define('RUNTIME_PATH', __DIR__ . '/../runtime/');

$app = new think\App();
$app->initialize();
