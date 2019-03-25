<?php
/**
 * Created by PhpStorm.
 * User: liupan
 * Date: 2018/6/17
 * Time: 下午9:00
 */

namespace app\admin\util;



use think\Exception;

class Auth
{
    private $model;
    private $sessionKey = 'admin_user';
    private $rememberMeKey = 'admin_remember_me';
    private $cookie_path = "/";
    public $rememberMeLifetime = 60*60*24*30; //记住登录时间默认一个月
    public function __construct()
    {
        $this->model = model('admin/Admin');
    }

    public function login($username, $password, $keepLogin)
    {
        $user = $this->model->with(['avatar' => function($query){
            $query->where('type', 'admin');
        }])->where('username', $username)->find();
        if(empty($user) || $user->status != 'normal')
        {
            throw new Exception('用户不存在或已被禁用');
        }
        if($user->password != $this->model->encryptPassword($password, $user->salt))
        {
            throw new Exception('密码不正确');
        }

        if(!isset($user->groupAccess->group->status) || $user->groupAccess->group->status != 'normal')
        {
            throw new Exception('用户所属用户组已被锁定');
        }
        $loginTime = time();
        $sessionData = $this->generateSessionData($user, $loginTime);
        session($this->sessionKey, $sessionData);
        $this->model->save(['login_time' => $loginTime], ['id' => $user->id]);
        \Hook::listen('admin_login_log', $sessionData); //记录登录日志
        if($keepLogin <= 0)
        {
            return true;
        }
        if($keepLogin)
        {
            $token = $this->model->updateRememberMe($user->id);
            if($token !== false)
            {
                setcookie($this->rememberMeKey, $token, time() + $this->rememberMeLifetime, $this->cookie_path);
            }
        }else{
            setcookie($this->rememberMeKey,'',time()-1, $this->cookie_path);
        }
        return true;
    }
    public function isLogin()
    {
        if(session('?'.$this->sessionKey))
        {
            return true;
        }
        if(!isset($_COOKIE[$this->rememberMeKey]))
        {
            return false;
        }
        $token = $_COOKIE[$this->rememberMeKey];
        $user = $this->model->with(['avatar' => function($query){
            $query->where('type', 'admin');
        }])->where('remember_me', $token)->find();
        if(empty($user) || !isset($user->remember_deadline) || $user->remember_deadline <= time())
        {
            return false;
        }
        session($this->sessionKey, $this->generateSessionData($user));
        return true;
    }

    public function getUser()
    {
        return session($this->sessionKey);
    }

    public function logout()
    {
        session($this->sessionKey, null);
        return true;
    }


    /**
     * 生成存入session的数据
     */
    public function generateSessionData($user, $loginTime = null)
    {
        $data = [];
        $data["id"] = $user->id;
        $data["username"] = $user->username;
        $data["nickname"] = $user->nickname;
        $data['group_id'] = $user->groupAccess->group->id;
        $data['group_name'] = $user->groupAccess->group->name;
        $data['rules'] = $user->groupAccess->group->rules;
        $data["login_time"] = $loginTime === null ? $user->login_time : $loginTime;
        $data["avatar"] = isset($user->avatar->url) ? $user->avatar->url : '';
        return $data;
    }
    /**
     * 检查指定规则名称是否有权限访问
     * @param $name
     * @param $isComplete bool 是否自动补全控制其名
     * @return bool
     */
    public function check($name, $isComplete = true)
    {
        $request = request();
        $name = trim(strtolower($name));
        $user = $request->currentUser;
        if($user['rules'] == '*')
        {
            return true;
        }
        if($isComplete && strpos($name, '/') === false)
        {
            $name = $request->controllerName.'/'.$name;
        }
        $userRules = user_rules();
        if(array_search($name, $userRules) === false)
        {
            return false;
        }
        return true;
    }
}