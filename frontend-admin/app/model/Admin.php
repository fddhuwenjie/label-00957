<?php
namespace app\model;

use think\Model;

/**
 * 管理员模型
 */
class Admin extends Model
{
    protected $table = 'admins';
    protected $pk = 'id';
    
    protected $hidden = ['password'];
    
    protected $type = [
        'id' => 'integer',
        'status' => 'integer',
    ];

    /**
     * 密码加密
     */
    public function setPasswordAttr($value): string
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    /**
     * 验证密码
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }
}
