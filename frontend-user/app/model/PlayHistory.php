<?php
namespace app\model;

use think\Model;

/**
 * 播放历史模型
 */
class PlayHistory extends Model
{
    protected $table = 'play_history';
    protected $pk = 'id';
    
    // 关闭自动时间戳（表使用played_at而非created_at）
    protected $autoWriteTimestamp = false;
    
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
