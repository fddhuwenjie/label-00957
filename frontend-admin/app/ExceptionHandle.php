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

class ExceptionHandle extends Handle
{
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    public function report(Throwable $exception): void
    {
        if (!$this->isIgnoreReport($exception)) {
            Log::error($exception->getMessage(), [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);
        }
        parent::report($exception);
    }

    public function render($request, Throwable $e): Response
    {
        if ($e instanceof ValidateException) {
            return error($e->getError(), 422);
        }
        if ($e instanceof ModelNotFoundException || $e instanceof DataNotFoundException) {
            return error('数据不存在', 404);
        }
        if ($e instanceof HttpException) {
            return error($e->getMessage(), $e->getStatusCode());
        }
        if ($request->isAjax() || $request->isJson()) {
            return error(env('APP_DEBUG') ? $e->getMessage() : '服务器错误', 500);
        }
        return parent::render($request, $e);
    }
}
