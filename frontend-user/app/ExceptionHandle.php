<?php
namespace app;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\facade\Log;
use think\Response;
use Throwable;

/**
 * 全局异常处理
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息的异常类列表
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * 记录异常信息
     */
    public function report(Throwable $exception): void
    {
        if (!$this->isIgnoreReport($exception)) {
            Log::error($exception->getMessage(), [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]);
        }
        parent::report($exception);
    }

    /**
     * 渲染异常
     */
    public function render($request, Throwable $e): Response
    {
        // 验证异常
        if ($e instanceof ValidateException) {
            return error($e->getError(), 422);
        }

        // 模型未找到
        if ($e instanceof ModelNotFoundException || $e instanceof DataNotFoundException) {
            return error('数据不存在', 404);
        }

        // HTTP异常
        if ($e instanceof HttpException) {
            return error($e->getMessage(), $e->getStatusCode());
        }

        // API请求返回JSON
        if ($request->isAjax() || $request->isJson()) {
            $message = env('APP_DEBUG') ? $e->getMessage() : '服务器错误';
            return error($message, 500);
        }

        return parent::render($request, $e);
    }
}
