<?php
/**
 * 公共函数文件
 */

use think\Response;

/**
 * 成功响应
 */
function success($data = [], string $message = 'success', int $code = 200): Response
{
    return json([
        'code' => $code,
        'message' => $message,
        'data' => $data
    ]);
}

/**
 * 失败响应
 */
function error(string $message = 'error', int $code = 400, $data = []): Response
{
    return json([
        'code' => $code,
        'message' => $message,
        'data' => $data
    ]);
}

/**
 * 验证URL格式
 * @param string $url 待验证的URL
 * @param bool $allowEmpty 是否允许为空
 * @return bool
 */
function validateUrl(string $url, bool $allowEmpty = true): bool
{
    if (empty($url)) {
        return $allowEmpty;
    }
    
    // 允许相对路径（以/开头）
    if (strpos($url, '/') === 0) {
        return true;
    }
    
    // 验证完整URL格式
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * 验证图片URL格式
 * @param string $url 待验证的URL
 * @param bool $allowEmpty 是否允许为空
 * @return bool
 */
function validateImageUrl(string $url, bool $allowEmpty = true): bool
{
    if (empty($url)) {
        return $allowEmpty;
    }
    
    if (!validateUrl($url, $allowEmpty)) {
        return false;
    }
    
    // 检查是否为常见图片格式
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico', 'bmp'];
    $path = parse_url($url, PHP_URL_PATH);
    if ($path) {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!empty($extension) && !in_array($extension, $allowedExtensions)) {
            return false;
        }
    }
    
    return true;
}

/**
 * 验证音频URL格式
 * @param string $url 待验证的URL
 * @param bool $allowEmpty 是否允许为空
 * @return bool
 */
function validateAudioUrl(string $url, bool $allowEmpty = true): bool
{
    if (empty($url)) {
        return $allowEmpty;
    }
    
    if (!validateUrl($url, $allowEmpty)) {
        return false;
    }
    
    // 检查是否为常见音频格式
    $allowedExtensions = ['mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a', 'wma'];
    $path = parse_url($url, PHP_URL_PATH);
    if ($path) {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!empty($extension) && !in_array($extension, $allowedExtensions)) {
            return false;
        }
    }
    
    return true;
}
