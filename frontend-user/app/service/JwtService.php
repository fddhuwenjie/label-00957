<?php
namespace app\service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * JWT服务
 */
class JwtService
{
    private static string $key = 'music_platform_secret_key_2024';
    private static string $algorithm = 'HS256';

    /**
     * 生成Token
     */
    public static function generate(int $userId, int $expire = 86400 * 7): string
    {
        $payload = [
            'iss' => 'music-platform',
            'iat' => time(),
            'exp' => time() + $expire,
            'uid' => $userId
        ];
        return JWT::encode($payload, self::$key, self::$algorithm);
    }

    /**
     * 验证Token
     */
    public static function verify(string $token): array
    {
        $decoded = JWT::decode($token, new Key(self::$key, self::$algorithm));
        return (array) $decoded;
    }
}
