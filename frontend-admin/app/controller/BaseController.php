<?php
namespace app\controller;

use think\App;

abstract class BaseController
{
    protected App $app;
    protected $request;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $this->app->request;
    }
}
