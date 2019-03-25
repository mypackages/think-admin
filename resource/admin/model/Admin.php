<?php
/**
 * Created by PhpStorm.
 * User: liupan
 * Email: 498501258@qq.com
 * Date: 2018/6/19
 * Time: 12:20
 */

namespace app\admin\model;

use app\admin\traits\ModelBase;
use app\admin\util\StringToolkit;
use think\Model;
use think\model\concern\SoftDelete;

class Admin extends Model
{
    // 引入软删除
    use SoftDelete;
    use ModelBase;
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
    //设置隐藏不显示的字段
    protected $hidden = ['password', 'salt', 'create_time', 'update_time', 'delete_time', 'remember_me', 'remember_deadline'];
    //设置允许输出的字段
    protected $visible = [];
    //只读字段
    protected $readonly = ['uuid'];


    /**
     * 创建列表搜索条件
     */
    protected function buildCondition($condition, $with, $fields)
    {
        if(isset($condition['group_id']) && $condition['group_id'] > 0)
        {
            $query = self::hasWhere('groupAccess', ['group_id' => $condition['group_id']]);
        }else{
            $query = $this;
        }
        if($with != '')
            $query = $query->with($with);
        if($fields != '')
            $query = $query->field($fields);
        if(isset($condition['status']) && $condition['status'] != '')
            $query = $query->where('status', $condition['status']);
        if(isset($condition['username']) && $condition['username'] != '')
            $query = $query->where('username','like', '%'.$condition['username'].'%');
        if(isset($condition['nickname']) && $condition['nickname'] != '')
            $query = $query->where('nickname','like', '%'.$condition['nickname'].'%');
        return $query;
    }

    /**
     * 重置用户密码
     */
    public function resetPassword($userId, $newPassword, $salt = '')
    {
        $password = $this->encryptPassword($newPassword, $salt);
        while (true)
        {
            $token = md5(StringToolkit::keyGen());
            $admin = $this->getByRememberMe($token);
            if(empty($admin)) break;
        }
        $ret = $this->where(['id' => $userId])->update(['password' => $password, 'remember_me' => $token]);
        return $ret;
    }

    /**
     * 生成用户密码
     */
    public function encryptPassword($password, $salt = '', $encrypt = 'md5')
    {
        return $encrypt($password . $salt);
    }


    /**
     * 根据id更新remember_me
     */
    public function updateRememberMe($id)
    {
        while (true)
        {
            $token = md5(StringToolkit::keyGen());
            $admin = $this->getByRememberMe($token);
            if(empty($admin)) break;
        }
        $rememberDeadline = time() + config('rememberMeLifetime');
        $data = [];
        $data['remember_me'] = $token;
        $data['remember_deadline'] = $rememberDeadline;
        if(!$this->save($data, ['id' => $id]))
        {
            return false;
        }
        return $token;
    }



    /**
     * 关联模型
     */
    public function groupAccess()
    {
        return $this->hasOne(AuthGroupAccess::class, 'user_id');
    }

    /**
     * 头像
     * @return \think\model\relation\BelongsTo
     */
    public function avatar()
    {
        return $this->belongsTo(File::class, 'uuid', 'uuid');
    }

}
