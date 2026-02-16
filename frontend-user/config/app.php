<?php
/**
 * 应用配置
 */
return [
    // 应用地址
    'app_host' => env('APP_HOST', ''),
    // 应用调试模式
    'app_debug' => env('APP_DEBUG', false),
    // 应用Trace
    'app_trace' => env('APP_TRACE', false),
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',
    // 默认语言
    'default_lang' => 'zh-cn',
    // 应用映射
    'app_map' => [],
    // 域名绑定
    'domain_bind' => [],
    // 禁止URL访问的应用列表
    'deny_app_list' => [],
    // 异常页面的模板文件
    'exception_tmpl' => app()->getThinkPath() . 'tpl/think_exception.tpl',
    // 错误显示信息
    'error_message' => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg' => false,
];
