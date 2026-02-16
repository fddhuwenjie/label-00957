<?php
namespace app\model;

use think\Model;

/**
 * 分类模型
 */
class Category extends Model
{
    protected $table = 'categories';
    protected $pk = 'id';
    
    protected $type = [
        'id' => 'integer',
        'sort_order' => 'integer',
        'status' => 'integer',
    ];
}
