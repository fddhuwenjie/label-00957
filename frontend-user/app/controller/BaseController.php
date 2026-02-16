<?php
namespace app\controller;

use think\App;

/**
 * 基础控制器
 */
abstract class BaseController
{
    protected App $app;
    protected $request;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $this->app->request;
        $this->initialize();
    }

    /**
     * 初始化
     */
    protected function initialize(): void
    {
    }
}
