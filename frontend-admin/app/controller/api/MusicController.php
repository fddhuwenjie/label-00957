<?php
namespace app\controller\api;

use app\controller\BaseController;
use app\model\Music;
use app\service\LogService;
use think\exception\ValidateException;

class MusicController extends BaseController
{
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

    public function create()
    {
        $data = input('post.');

        $required = [
            'title' => '音乐标题',
            'artist' => '艺术家',
            'audio_url' => '音频地址'
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
