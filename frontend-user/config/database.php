<?php
/**
 * 数据库配置 - 支持多数据库类型
 * 支持: mysql, pgsql, sqlite
 */
return [
    'default' => env('DB_CONNECTION', 'mysql'),
    'connections' => [
        // MySQL 配置（Docker 环境变量优先，避免 volume 挂载 .env 导致 DB_HOST=127.0.0.1 无法连接 mysql 容器）
        'mysql' => [
            'type' => 'mysql',
            'hostname' => getenv('DB_HOST') ?: env('DB_HOST', '127.0.0.1'),
            'database' => getenv('DB_DATABASE') ?: env('DB_DATABASE', 'music_platform'),
            'username' => getenv('DB_USERNAME') ?: env('DB_USERNAME', 'root'),
            'password' => (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : env('DB_PASSWORD', '')),
            'hostport' => getenv('DB_PORT') ?: env('DB_PORT', '3306'),
            'charset' => 'utf8mb4',
            'prefix' => '',
            'debug' => env('APP_DEBUG', false),
        ],
        // PostgreSQL 配置
        'pgsql' => [
            'type' => 'pgsql',
            'hostname' => env('DB_HOST', '127.0.0.1'),
            'database' => env('DB_DATABASE', 'music_platform'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', ''),
            'hostport' => env('DB_PORT', '5432'),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => env('DB_SCHEMA', 'public'),
            'debug' => env('APP_DEBUG', false),
        ],
        // SQLite 配置
        'sqlite' => [
            'type' => 'sqlite',
            'database' => env('DB_DATABASE', runtime_path() . 'database.db'),
            'prefix' => '',
            'debug' => env('APP_DEBUG', false),
        ],
    ],
];
