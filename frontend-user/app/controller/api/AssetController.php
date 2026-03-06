<?php
namespace app\controller\api;

use app\controller\BaseController;
use think\Response;

/**
 * 动态资源控制器
 * 实现动态加载CSS/JS，保护源码
 */
class AssetController extends BaseController
{
    /**
     * 动态加载CSS
     */
    public function css()
    {
        $file = input('file', '', 'trim');
        
        // 允许路径中包含斜杠（如 pages/home）
        if (empty($file) || !preg_match('/^[\w\-\/]+$/', $file)) {
            return $this->notFound();
        }

        $path = app()->getRootPath() . 'public/assets/css/' . $file . '.css';
        
        if (!file_exists($path)) {
            return $this->notFound();
        }

        $content = file_get_contents($path);
        
        // CSS压缩处理
        $content = $this->minifyCss($content);
        
        $headers = $this->buildCacheHeaders('text/css; charset=utf-8', $path);
        return Response::create($content, 'html', 200)->header($headers);
    }

    /**
     * 动态加载JS
     */
    public function js()
    {
        $file = input('file', '', 'trim');
        
        if (empty($file) || !preg_match('/^[\w\-\/]+$/', $file)) {
            return $this->notFound();
        }

        $path = app()->getRootPath() . 'public/assets/js/' . $file . '.js';
        
        if (!file_exists($path)) {
            return $this->notFound();
        }

        $content = file_get_contents($path);
        
        // 混淆处理（简单压缩）
        $content = $this->minifyJs($content);
        
        $headers = $this->buildCacheHeaders('application/javascript; charset=utf-8', $path);
        return Response::create($content, 'html', 200)->header($headers);
    }

    /**
     * 构建合理的缓存响应头，基于文件修改时间生成 ETag
     */
    private function buildCacheHeaders(string $contentType, string $filePath): array
    {
        $mtime = filemtime($filePath);
        $etag = '"' . md5($filePath . $mtime) . '"';

        return [
            'Content-Type'        => $contentType,
            'Cache-Control'       => 'public, max-age=86400',
            'ETag'                => $etag,
            'Last-Modified'       => gmdate('D, d M Y H:i:s', $mtime) . ' GMT',
            'Expires'             => gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT',
            'X-Content-Type-Options' => 'nosniff',
        ];
    }

    /**
     * 简单CSS压缩
     */
    private function minifyCss(string $content): string
    {
        // 移除注释
        $content = preg_replace('/\/\*[\s\S]*?\*\//', '', $content);
        // 移除多余空白
        $content = preg_replace('/\s+/', ' ', $content);
        // 移除选择器前后空格
        $content = preg_replace('/\s*([{};:,>+~])\s*/', '$1', $content);
        return trim($content);
    }

    /**
     * 简单JS处理（安全模式：只去除BOM和空行，不做破坏JS结构的压缩）
     * 注意：用正则暴力压缩JS会破坏字符串/模板字面量/正则字面量中的内容，
     * 例如 `${url}?${query}`、/\s/g、'http://...' 等，导致脚本报错无法执行。
     */
    private function minifyJs(string $content): string
    {
        // 去除 UTF-8 BOM
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            $content = substr($content, 3);
        }
        // 只移除完全空白的行，保留代码结构
        $content = preg_replace('/^\s*[\r\n]/m', '', $content);
        return trim($content);
    }

    /**
     * 404响应
     */
    private function notFound(): Response
    {
        return Response::create('资源不存在', 'html', 404);
    }
}
