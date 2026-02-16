<?php
/**
 * 中间件配置
 */
return [
    // 全局中间件
    'global' => [
        \app\middleware\Cors::class,
    ],
    // 别名或分组
    'alias' => [
        'admin_auth' => \app\middleware\AdminAuth::class,
    ],
    // 优先级设置
    'priority' => [],
];
