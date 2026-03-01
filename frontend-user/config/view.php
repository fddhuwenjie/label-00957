<?php
/**
 * 视图配置
 */
return [
    // 模板引擎类型（使用 ThinkPHP 模板引擎，支持 {extend}/{block} 等语法）
    'type' => 'Think',
    // 模板路径
    'view_path' => app()->getAppPath() . 'view/',
    // 模板后缀
    'view_suffix' => 'php',
    // 模板文件名分隔符
    'view_depr' => DIRECTORY_SEPARATOR,
];
