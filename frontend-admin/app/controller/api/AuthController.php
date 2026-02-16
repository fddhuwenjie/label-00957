<?php
namespace app\controller\api;

use app\controller\BaseController;
use app\model\Admin;
use app\service\JwtService;
use app\service\LogService;
use app\service\CryptoService;
use think\facade\Log;

class AuthController extends BaseController
{
    /**
     * 获取公钥（前端加密用）
     */
    public function publicKey()
    {
        try {
            $publicKey = CryptoService::getPublicKey();
            return success(['public_key' => $publicKey]);
        } catch (\Exception $e) {
            Log::error('获取公钥失败: ' . $e->getMessage());
            return error('获取公钥失败', 500);
        }
    }

    public function login()
    {
        try {
            // 获取原始请求体
            $content = $this->request->getContent();
            
            // 尝试解析 JSON
            $data = [];
            if (!empty($content)) {
                $decoded = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $data = $decoded;
                }
            }
            
            // 如果 JSON 解析失败，尝试从 POST 获取
            if (empty($data)) {
                $data = input('post.');
            }
            
            $username = isset($data['username']) ? trim($data['username']) : '';
            $encryptedPassword = $data['password'] ?? '';
            
            // 解密密码
            try {
                $password = CryptoService::decrypt($encryptedPassword);
            } catch (\Exception $e) {
                Log::error('密码解密失败: ' . $e->getMessage());
                return error('密码解密失败，请刷新页面重试', 400);
            }

            if (empty($username) || empty($password)) {
                return error('请输入用户名和密码', 422);
            }

            $admin = Admin::where('username', $username)->find();

            if (!$admin || !$admin->verifyPassword($password)) {
                return error('用户名或密码错误', 401);
            }

            if ($admin->status !== 1) {
                return error('账号已被禁用', 403);
            }

            // 更新登录时间
            $admin->last_login = date('Y-m-d H:i:s');
            $admin->save();

            $token = JwtService::generate($admin->id);

            Log::info('管理员登录', ['admin_id' => $admin->id, 'username' => $username]);
            
            // 记录日志（捕获异常避免影响登录）
            try {
                LogService::record($admin->id, '系统', '登录', '管理员登录系统');
            } catch (\Exception $e) {
                Log::error('记录登录日志失败: ' . $e->getMessage());
            }

            return success([
                'token' => $token,
                'admin' => $admin->hidden(['password'])->toArray()
            ], '登录成功');
        } catch (\Exception $e) {
            Log::error('登录异常: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return error('登录失败: ' . $e->getMessage(), 500);
        }
    }

    public function logout()
    {
        $adminId = $this->request->adminId ?? 0;
        if ($adminId > 0) {
            LogService::record($adminId, '系统', '退出', '管理员退出系统');
        }
        return success(null, '退出成功');
    }

    public function info()
    {
        $adminId = $this->request->adminId;
        $admin = Admin::find($adminId);
        
        if (!$admin) {
            return error('管理员不存在', 404);
        }

        return success($admin);
    }
}
