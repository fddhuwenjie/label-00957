<?php
namespace app\model;

use think\Model;

/**
 * 音乐模型
 */
class Music extends Model
{
    protected $table = 'music';
    protected $pk = 'id';
    
    protected $type = [
        'id' => 'integer',
        'category_id' => 'integer',
        'duration' => 'integer',
        'play_count' => 'integer',
        'status' => 'integer',
        'user_id' => 'integer',
    ];

    /**
     * 分类关联
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
