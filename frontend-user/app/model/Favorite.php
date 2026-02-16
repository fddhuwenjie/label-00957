<?php
namespace app\model;

use think\Model;

/**
 * 收藏模型
 */
class Favorite extends Model
{
    protected $table = 'favorites';
    protected $pk = 'id';
    
    protected $type = [
        'id' => 'integer',
        'user_id' => 'integer',
        'music_id' => 'integer',
    ];

    /**
     * 音乐关联
     */
    public function music()
    {
        return $this->belongsTo(Music::class, 'music_id');
    }
}
