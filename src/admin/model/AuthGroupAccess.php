<?php
/**
 * Created by PhpStorm.
 * User: liupan
 * Email: 498501258@qq.com
 * Date: 2018/6/25
 * Time: 10:12
 */

namespace app\admin\model;


use think\Model;

class AuthGroupAccess extends Model
{
    public function group()
    {
        return $this->belongsTo(AuthGroup::class, 'group_id', 'id');
    }
}