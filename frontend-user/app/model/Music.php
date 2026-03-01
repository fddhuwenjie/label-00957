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
     * 将 admin 上传的相对路径补全为完整 URL
     */
    private static function resolveUploadUrl(string $value): string
    {
        // /uploads/ 开头的文件存放在 admin 端，需要补全 admin 域名
        if (str_starts_with($value, '/uploads/')) {
            $adminUrl = rtrim(env('ADMIN_URL', 'http://localhost:8082'), '/');
            return $adminUrl . $value;
        }
        return $value;
    }

    /**
     * 获取封面URL - 确保返回有效的封面路径
     */
    public function getCoverUrlAttr($value): string
    {
        if (empty($value)) {
            return self::DEFAULT_COVER;
        }

        return self::resolveUploadUrl($value);
    }

    /**
     * 获取音频URL - 将 admin 上传路径补全为完整 URL
     */
    public function getAudioUrlAttr($value): string
    {
        if (empty($value)) {
            return $value;
        }

        return self::resolveUploadUrl($value);
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
