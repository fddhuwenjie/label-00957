<?php
namespace app\controller\api;

use app\controller\BaseController;
use app\model\Music;
use app\service\LogService;
use think\exception\ValidateException;
use think\facade\Filesystem;

class MusicController extends BaseController
{
    // 允许的音频格式
    const AUDIO_EXTS = ['mp3', 'wav', 'flac', 'aac', 'ogg', 'm4a'];
    // 允许的图片格式
    const IMAGE_EXTS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    // 音频最大大小 20MB
    const AUDIO_MAX_SIZE = 20 * 1024 * 1024;
    // 图片最大大小 2MB
    const IMAGE_MAX_SIZE = 2 * 1024 * 1024;

    public function list()
    {
        $page = input('page', 1, 'intval');
        $limit = input('limit', 20, 'intval');
        $keyword = input('keyword', '', 'trim');
        $categoryId = input('category_id', 0, 'intval');
        $status = input('status', -1, 'intval');

        $query = Music::with(['category']);

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->whereLike('title', "%{$keyword}%")
                  ->whereOr('artist', 'like', "%{$keyword}%");
            });
        }

        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }

        if ($status >= 0) {
            $query->where('status', $status);
        }

        $list = $query->order('id', 'desc')
            ->paginate(['page' => $page, 'list_rows' => $limit]);

        return success($list);
    }

    public function detail()
    {
        $id = input('id', 0, 'intval');
        $music = Music::with(['category'])->find($id);

        if (!$music) {
            return error('音乐不存在', 404);
        }

        return success($music);
    }

    /**
     * 上传音频文件
     */
    public function uploadAudio()
    {
        $file = request()->file('audio');
        if (!$file) {
            return error('请选择音频文件', 422);
        }

        // 检查文件大小
        if ($file->getSize() > self::AUDIO_MAX_SIZE) {
            return error('音频文件不能超过20MB', 422);
        }

        // 检查文件格式
        $ext = strtolower($file->getOriginalExtension());
        if (!in_array($ext, self::AUDIO_EXTS)) {
            return error('仅支持 ' . implode('/', self::AUDIO_EXTS) . ' 格式', 422);
        }

        try {
            $saveName = date('Ymd') . '/' . md5(uniqid()) . '.' . $ext;
            $file->move(public_path() . 'uploads/audio/' . dirname($saveName), basename($saveName));
            $url = '/uploads/audio/' . $saveName;

            // 尝试获取音频时长
            $duration = $this->getAudioDuration(public_path() . 'uploads/audio/' . $saveName);

            return success([
                'url' => $url,
                'duration' => $duration
            ], '上传成功');
        } catch (\Exception $e) {
            return error('上传失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 上传封面图片
     */
    public function uploadCover()
    {
        $file = request()->file('cover');
        if (!$file) {
            return error('请选择封面图片', 422);
        }

        // 检查文件大小
        if ($file->getSize() > self::IMAGE_MAX_SIZE) {
            return error('封面图片不能超过2MB', 422);
        }

        // 检查文件格式
        $ext = strtolower($file->getOriginalExtension());
        if (!in_array($ext, self::IMAGE_EXTS)) {
            return error('仅支持 ' . implode('/', self::IMAGE_EXTS) . ' 格式', 422);
        }

        try {
            $saveName = date('Ymd') . '/' . md5(uniqid()) . '.' . $ext;
            $file->move(public_path() . 'uploads/covers/' . dirname($saveName), basename($saveName));
            $url = '/uploads/covers/' . $saveName;

            return success(['url' => $url], '上传成功');
        } catch (\Exception $e) {
            return error('上传失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取音频时长（秒）
     */
    private function getAudioDuration(string $filePath): int
    {
        // 尝试用 getID3 获取时长（如果可用）
        if (class_exists('\getID3')) {
            try {
                $getID3 = new \getID3();
                $info = $getID3->analyze($filePath);
                if (isset($info['playtime_seconds'])) {
                    return (int) round($info['playtime_seconds']);
                }
            } catch (\Exception $e) {}
        }

        // 对 MP3 文件尝试简单解析
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($ext === 'mp3') {
            return $this->estimateMp3Duration($filePath);
        }

        return 0;
    }

    /**
     * 简单估算 MP3 时长
     */
    private function estimateMp3Duration(string $filePath): int
    {
        try {
            $fileSize = filesize($filePath);
            $fp = fopen($filePath, 'rb');
            if (!$fp) return 0;

            // 查找第一个有效帧头
            $header = '';
            $offset = 0;
            $maxSearch = min(4096, $fileSize);

            while ($offset < $maxSearch) {
                fseek($fp, $offset);
                $bytes = fread($fp, 4);
                if (strlen($bytes) < 4) break;

                // 检查帧同步 (0xFFE0)
                $b0 = ord($bytes[0]);
                $b1 = ord($bytes[1]);
                if ($b0 === 0xFF && ($b1 & 0xE0) === 0xE0) {
                    $header = $bytes;
                    break;
                }
                // 跳过 ID3v2 标签
                if ($offset === 0 && substr($bytes, 0, 3) === 'ID3') {
                    fseek($fp, 6);
                    $sizeBytes = fread($fp, 4);
                    $tagSize = ((ord($sizeBytes[0]) & 0x7F) << 21) |
                               ((ord($sizeBytes[1]) & 0x7F) << 14) |
                               ((ord($sizeBytes[2]) & 0x7F) << 7) |
                               (ord($sizeBytes[3]) & 0x7F);
                    $offset = $tagSize + 10;
                    continue;
                }
                $offset++;
            }

            fclose($fp);

            if (empty($header)) return 0;

            // 解析比特率
            $b1 = ord($header[1]);
            $b2 = ord($header[2]);

            $version = ($b1 >> 3) & 0x03;
            $layer = ($b1 >> 1) & 0x03;
            $bitrateIndex = ($b2 >> 4) & 0x0F;

            // MPEG1 Layer3 比特率表
            $bitrates = [0, 32, 40, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320, 0];

            if ($version === 3 && $layer === 1 && $bitrateIndex > 0 && $bitrateIndex < 15) {
                $bitrate = $bitrates[$bitrateIndex] * 1000;
                $audioSize = $fileSize - $offset;
                return (int) round(($audioSize * 8) / $bitrate);
            }

            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function create()
    {
        $data = input('post.');

        $required = [
            'title' => '音乐标题',
            'artist' => '艺术家',
            'audio_url' => '音频文件'
        ];
        foreach ($required as $field => $label) {
            if (empty($data[$field])) {
                return error("{$label}不能为空", 422);
            }
        }

        $music = Music::create([
            'title' => $data['title'],
            'artist' => $data['artist'],
            'album' => $data['album'] ?? '',
            'category_id' => $data['category_id'] ?? 0,
            'cover_url' => $data['cover_url'] ?? '',
            'audio_url' => $data['audio_url'],
            'lyrics' => $data['lyrics'] ?? '',
            'duration' => $data['duration'] ?? 0,
            'status' => $data['status'] ?? 1
        ]);

        LogService::record($this->request->adminId, '音乐管理', '新增', "新增音乐: {$music->title}");

        return success($music, '创建成功');
    }

    public function update()
    {
        $id = input('id', 0, 'intval');
        $data = input('post.');

        $music = Music::find($id);
        if (!$music) {
            return error('音乐不存在', 404);
        }

        $allowFields = ['title', 'artist', 'album', 'category_id', 'cover_url', 'audio_url', 'lyrics', 'duration', 'status'];
        $updateData = array_intersect_key($data, array_flip($allowFields));

        $music->save($updateData);

        LogService::record($this->request->adminId, '音乐管理', '编辑', "编辑音乐: {$music->title}");

        return success($music, '更新成功');
    }

    public function delete()
    {
        $id = input('id', 0, 'intval');

        $music = Music::find($id);
        if (!$music) {
            return error('音乐不存在', 404);
        }

        $title = $music->title;
        $music->delete();

        LogService::record($this->request->adminId, '音乐管理', '删除', "删除音乐: {$title}");

        return success(null, '删除成功');
    }

    public function updateStatus()
    {
        $id = input('id', 0, 'intval');
        $status = input('post.status', 0, 'intval');

        $music = Music::find($id);
        if (!$music) {
            return error('音乐不存在', 404);
        }

        $music->status = $status;
        $music->save();

        $action = $status === 1 ? '上架' : '下架';
        LogService::record($this->request->adminId, '音乐管理', $action, "{$action}音乐: {$music->title}");

        return success(null, '操作成功');
    }
}
