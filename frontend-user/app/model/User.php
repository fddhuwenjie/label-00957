<?php
namespace app\model;

use think\Model;

/**
 * 用户模型
 */
class User extends Model
{
    protected $table = 'users';
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

    /**
     * 收藏关联
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'user_id');
    }
}
