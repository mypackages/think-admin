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
use think\exception\HttpResponseException;
use think\facade\Config;
use think\Response;

class checkPermission
{
    public function handle($request, \Closure $next)
    {
        $action = strtolower($request->action());
        //权限判断
        $noCheckAuth = false;
        if(isset($request->noAuthAction) && is_array($request->noAuthAction))
        {
            foreach ($request->noAuthAction as $k => $v)
            {
                if($action == trim(strtolower($v))) // 不需要验证的action
                {
                    $noCheckAuth = true;
                    break;
                }
            }
        }
        if(!$noCheckAuth && !app('adminAuth')->check($action))
        {
            if($request->isAjax() || $request->isPost())
            {
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode(Result::fail('无权限访问', '', 500)));
            }
            $type = Config::get('default_return_type');
            if ('html' == strtolower($type)) {
                $type = 'jump';
            }
            $result = ['code' => 0, 'msg'  => '无权限访问', 'data' => '', 'url'  => 'javascript:history.back(-1);', 'wait' => 3];
            $response = Response::create($result, $type)->header([])->options(['jump_template' => Config::get('dispatch_error_tmpl')]);
            throw new HttpResponseException($response);
        }
        return $next($request);
    }
}