<?php
namespace app\service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private static string $key = 'music_admin_secret_key_2024';
    private static string $algorithm = 'HS256';

    public static function generate(int $adminId, int $expire = 86400): string
    {
        $payload = [
            'iss' => 'music-admin',
            'iat' => time(),
            'exp' => time() + $expire,
            'aid' => $adminId
        ];
        return JWT::encode($payload, self::$key, self::$algorithm);
    }

    public static function verify(string $token): array
    {
        $decoded = JWT::decode($token, new Key(self::$key, self::$algorithm));
        return (array) $decoded;
    }
}
