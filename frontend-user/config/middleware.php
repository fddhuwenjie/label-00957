<?php
/**
 * 中间件配置
 */
return [
    // 全局中间件
    'global' => [
        \app\middleware\Cors::class,
    ],
    // 别名
    'alias' => [
        'auth' => \app\middleware\Auth::class,
    ],
];
