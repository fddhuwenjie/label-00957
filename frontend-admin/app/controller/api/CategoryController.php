<?php
namespace app\controller\api;

use app\controller\BaseController;
use app\model\Category;
use app\service\LogService;

class CategoryController extends BaseController
{
    public function list()
    {
        $list = Category::order('sort_order', 'asc')->select();
        return success($list);
    }

    public function create()
    {
        $data = input('post.');

        if (empty($data['name'])) {
            return error('分类名称不能为空', 422);
        }

        $category = Category::create([
            'name' => $data['name'],
            'icon' => $data['icon'] ?? '',
            'sort_order' => $data['sort_order'] ?? 0,
            'status' => $data['status'] ?? 1
        ]);

        LogService::record($this->request->adminId, '分类管理', '新增', "新增分类: {$category->name}");

        return success($category, '创建成功');
    }

    public function update()
    {
        $id = input('id', 0, 'intval');
        $data = input('post.');

        $category = Category::find($id);
        if (!$category) {
            return error('分类不存在', 404);
        }

        $allowFields = ['name', 'icon', 'sort_order', 'status'];
        $updateData = array_intersect_key($data, array_flip($allowFields));

        $category->save($updateData);

        LogService::record($this->request->adminId, '分类管理', '编辑', "编辑分类: {$category->name}");

        return success($category, '更新成功');
    }

    public function delete()
    {
        $id = input('id', 0, 'intval');

        $category = Category::find($id);
        if (!$category) {
            return error('分类不存在', 404);
        }

        $name = $category->name;
        $category->delete();

        LogService::record($this->request->adminId, '分类管理', '删除', "删除分类: {$name}");

        return success(null, '删除成功');
    }
}
