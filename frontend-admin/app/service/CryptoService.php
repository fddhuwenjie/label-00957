<?php
namespace app\service;

class CryptoService
{
    /**
     * 生成 RSA 密钥对
     */
    public static function generateKeyPair(): array
    {
        $config = [
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];
        
        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $privateKey);
        $publicKey = openssl_pkey_get_details($res)['key'];
        
        return [
            'private_key' => $privateKey,
            'public_key' => $publicKey
        ];
    }

    /**
     * 获取公钥（返回给前端）
     */
    public static function getPublicKey(): string
    {
        $keyFile = runtime_path() . 'keys/public.pem';
        if (file_exists($keyFile)) {
            return file_get_contents($keyFile);
        }
        
        // 首次使用时生成密钥对
        self::initKeys();
        return file_get_contents($keyFile);
    }

    /**
     * 获取私钥
     */
    private static function getPrivateKey(): string
    {
        $keyFile = runtime_path() . 'keys/private.pem';
        if (file_exists($keyFile)) {
            return file_get_contents($keyFile);
        }
        
        self::initKeys();
        return file_get_contents($keyFile);
    }

    /**
     * 初始化密钥对
     */
    private static function initKeys(): void
    {
        $keyDir = runtime_path() . 'keys';
        if (!is_dir($keyDir)) {
            mkdir($keyDir, 0755, true);
        }
        
        $keys = self::generateKeyPair();
        file_put_contents($keyDir . '/private.pem', $keys['private_key']);
        file_put_contents($keyDir . '/public.pem', $keys['public_key']);
    }

    /**
     * RSA 解密（使用私钥解密前端传来的数据）
     */
    public static function decrypt(string $encrypted): string
    {
        $privateKey = self::getPrivateKey();
        $encrypted = base64_decode($encrypted);
        
        $decrypted = '';
        if (openssl_private_decrypt($encrypted, $decrypted, $privateKey)) {
            return $decrypted;
        }
        
        throw new \Exception('解密失败');
    }

    /**
     * RSA 加密（使用公钥加密，主要用于测试）
     */
    public static function encrypt(string $data): string
    {
        $publicKey = self::getPublicKey();
        $encrypted = '';
        
        if (openssl_public_encrypt($data, $encrypted, $publicKey)) {
            return base64_encode($encrypted);
        }
        
        throw new \Exception('加密失败');
    }
}
