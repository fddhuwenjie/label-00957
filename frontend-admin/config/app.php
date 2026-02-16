<?php
return [
    'app_host' => env('APP_HOST', ''),
    'app_debug' => env('APP_DEBUG', false),
    'default_timezone' => 'Asia/Shanghai',
    'default_lang' => 'zh-cn',
    'exception_tmpl' => app()->getThinkPath() . 'tpl/think_exception.tpl',
    'error_message' => '系统错误',
    'show_error_msg' => false,
];
