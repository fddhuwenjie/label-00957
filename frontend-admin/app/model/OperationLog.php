<?php
namespace app\model;

use think\Model;

/**
 * 操作日志模型
 */
class OperationLog extends Model
{
    protected $table = 'operation_logs';
    protected $pk = 'id';
    
    protected $type = [
        'id' => 'integer',
        'admin_id' => 'integer',
    ];

    /**
     * 管理员关联
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
