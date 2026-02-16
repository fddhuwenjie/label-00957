<?php
/**
 * 数据库配置 - 支持多数据库类型
 * 支持: mysql, pgsql, sqlite
 */
return [
    'default' => env('DB_CONNECTION', 'mysql'),
    'connections' => [
        // MySQL 配置
        'mysql' => [
            'type' => 'mysql',
            'hostname' => env('DB_HOST', '127.0.0.1'),
            'database' => env('DB_DATABASE', 'music_platform'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'hostport' => env('DB_PORT', '3306'),
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
