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
     * 默认封面图片
     */
    const DEFAULT_COVER = '/assets/images/cover-default.svg';

    /**
     * 分类关联
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * 增加播放次数
     */
    public function incrementPlayCount(): void
    {
        $this->play_count = $this->play_count + 1;
        $this->save();
    }

    /**
     * 获取封面URL - 确保返回有效的封面路径
     */
    public function getCoverUrlAttr($value): string
    {
        // 如果为空，返回默认封面
        if (empty($value)) {
            return self::DEFAULT_COVER;
        }
        
        return $value;
    }

    /**
     * 格式化时长
     */
    public function getDurationTextAttr($value, $data): string
    {
        $duration = $data['duration'] ?? 0;
        $minutes = floor($duration / 60);
        $seconds = $duration % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
