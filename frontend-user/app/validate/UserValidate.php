<?php
namespace app\validate;

use think\Validate;

/**
 * 用户验证器
 */
class UserValidate extends Validate
{
    protected $rule = [
        'email' => 'require|email|unique:users',
        'password' => 'require|min:6|max:20',
        'nickname' => 'max:50',
    ];

    protected $message = [
        'email.require' => '邮箱不能为空',
        'email.email' => '邮箱格式不正确',
        'email.unique' => '该邮箱已被注册',
        'password.require' => '密码不能为空',
        'password.min' => '密码长度不能少于6位',
        'password.max' => '密码长度不能超过20位',
        'nickname.max' => '昵称长度不能超过50个字符',
    ];

    protected $scene = [
        'register' => ['email', 'password'],
        'login' => ['email' => 'require|email', 'password' => 'require'],
        'update' => ['nickname'],
    ];
}
