<?php
/**
 * Created by PhpStorm.
 * User: liupan
 * Email: 498501258@qq.com
 * Date: 2019/3/21
 * Time: 11:23
 */

namespace app\admin\middleware;


use app\admin\util\Result;

class checkLogin
{
    public function handle($request, \Closure $next)
    {
        $request->controllerName = $controller = strtolower($request->controller());
        $request->actionName = strtolower($request->action()); //模块下函数里面有用到
        if($controller == 'login') //登陆页不鉴权
        {
            return $next($request);
        }
        $auth = app('adminAuth');
        $request->isLogin = $auth->isLogin();
        if(!$request->isLogin) //未登录跳到登陆页
        {
            if($request->isAjax())
            {
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode(Result::fail('登录失效请重新登录', '', 500)));
            }
            return redirect('Login/index');
        }
        $request->currentUser = $request->isLogin ? $auth->getUser() : null;
        return $next($request);
    }
}