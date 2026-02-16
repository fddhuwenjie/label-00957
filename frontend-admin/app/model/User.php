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
}
