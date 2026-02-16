<?php
namespace app\controller;

use think\facade\View;

class IndexController extends BaseController
{
    public function index()
    {
        return View::fetch('index/index');
    }

    public function login()
    {
        return View::fetch('index/login');
    }
}
