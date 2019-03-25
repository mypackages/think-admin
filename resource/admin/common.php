<?php
/**
 * Created by PhpStorm.
 * User: liupan
 * Email: 498501258@qq.com
 * Date: 2018/6/25
 * Time: 14:01
 */

/**
 * 获取左侧菜单
 * @author liupan
 * @return array
 */
function get_menu()
{
    static $cache;
    if($cache != null)
    {
        return $cache;
    }
    $allRules = all_auth_rule();
    $auth = app('adminAuth');
    foreach ($allRules as $k => $v)
    {
        if($v['status'] != 'normal' || $v['is_menu'] != 1 || !$auth->check($v["name"], false))
        {
            unset($allRules[$k]);
        }
    }
    $cache = $allRules;
    return $cache;
}

/**
 * 重组菜单的上下级关系
 * @author liupan
 * @return array
 */
function menu_tree()
{
    static $menu;
    if($menu != null)
    {
        return $menu;
    }
    $menu = find_current_menu(array_tree(get_menu()));
    return $menu;
}
/**
 * 寻找当前菜单及上级菜单的选中状态
 * @author liupan
 * @param $menu
 * @return array
 */
function find_current_menu($menu)
{
    static $request;
    static $path;
    if($request == null)
    {
        $request = request();
    }
    if($path == null)
    {
        $path = $request->controllerName.'/'.$request->actionName;
        $path = find_current_menu_name($path);
    }
    menu_child_active($menu, $path);
    return $menu;
}

/**
 * 根据rule节点的name寻找最近一层菜单的name
 * @author liupan
 * @param $name
 * @return string
 */
function find_current_menu_name($name)
{
    $allRules = all_auth_rule();
    foreach ($allRules as $k => $v)
    {
        if(trim(strtolower($v['name'])) != trim(strtolower($name)))
        {
            continue;
        }
        if($v['is_menu'] == 1)
        {
            return $v['name'];
        }
        if(isset($allRules[$v['pid']]['name']))
        {
            return find_current_menu_name($allRules[$v['pid']]['name']);
        }
        return '';
    }
    return '';
}
/**
 * 判断子菜单是否选中
 * @author liupan
 * @param $menu
 * @param $path
 * @return bool
 */
function menu_child_active(&$menu, $path)
{

    foreach ($menu as $k => &$v)
    {
        if(trim(strtolower($v['name'])) == trim(strtolower($path)))
        {
            $v['is_active'] = true;
            return true;
        }

        if(!empty($v['child']))
        {
            $has = menu_child_active($v['child'], $path);
            if($has)
            {
                $v['is_active'] = true;
                return true;
            }
        }
    }
    return false;
}

/**
 * 获取当前菜单及所有父级菜单
 * @author liupan
 * @return array
 */
function get_position()
{
    static $cache;
    if($cache != null)
    {
        return $cache;
    }
    $list = position_tree(menu_tree(), $current);
    $cache = [];
    $cache['tree'] = $list;
    $cache['current'] = empty($current) ? [] : $current;
    return $cache;
}

/**
 * 获取当前菜单组合成新数组
 * @author liupan
 * @param $activeMenu
 * @param $current
 * @return array
 */
function position_tree($activeMenu, &$current)
{
    static $list;
    if($list == null)
        $list = [];
    foreach ($activeMenu as $k => $v)
    {
        if(!isset($v['is_active']) || !$v['is_active'])
        {
            continue;
        }
        $current = $v;
        $row = $v;
        if(isset($row['child'])) unset($row['child']);
        $list[] = $row;
        if(isset($v['child']) && !empty($v['child']))
        {
            position_tree($v['child'], $current);
        }
    }
    return $list;
}

/**
 * 生成列表左上角的按钮
 * @author liupan
 * @param $param
 * @return string
 */
function build_toolbar($param)
{
    if(!is_array($param) || empty($param))
        return '';
    $toolbar = '';
    $auth = app('adminAuth');
    foreach ($param as $name => $alink)
    {
        if(!$auth->check($name))
        {
            continue;
        }
        if(trim($name) == 'add' && trim($alink) == '')
        {
            $alink = '<a href="javascript:;" class="btn btn-success btn-add clickModal" data-url="'.url('add').'" title="添加"><i class="fa fa-plus"></i> 添加</a>';
        }
        if(trim($name) == 'delete' && trim($alink) == '')
        {
            $alink = '<a href="javascript:;" class="btn btn-danger btn-del btn-disabled  batch_delete" v-if="batchDelete" v-on:click="batchDelete"   title="删除"><i class="fa fa-trash"></i> 删除</a>';
        }
        if(trim($name) == 'export' && trim($alink) == '')
        {
            $alink = '<a href="javascript:;" class="btn btn-info  data-export" data-url="'.url('export').'" title="导出"><i class="fa  fa-download"></i> 导出</a>';
        }
        $toolbar .= $alink.' ';
    }
    return $toolbar;
}

/**
 * 生成列表数据操作栏的js变量
 * @author liupan
 * @param $param
 * @return string
 */
function build_operate($param)
{
    $auth = app('adminAuth');
    $html = '<script>'.PHP_EOL;
    $html .= '      var build_operate = {}'.PHP_EOL;
    foreach ($param as $k => $v)
    {
        if(!$auth->check($k))
        {
            continue;
        }

        if(trim($k) == 'edit' && trim($v) == '')
        {
            $v = '<a data-url="'.url('edit', ['id' => 'id_value']).'" class="btn btn-xs btn-success btn-editone clickModal" data-toggle="tooltip" title="编辑"  ><i class="fa fa-pencil"></i></a>';
        }
        if(trim($k) == 'delete' && trim($v) == '')
        {
            $v = "<a href='javascript:;' class='btn btn-xs btn-danger btn-delone clickConfirm' title='删除' confirm-url='".url('delete', ['id' => 'id_value'])."'  confirm-message='确定要删除吗' confirm-success='删除成功' confirm-error='删除失败' confirm-method='post'><i class='fa fa-trash'></i></a>";
        }
        $alink = str_replace('\'', '"', $v);
        $html .= "      build_operate.index".md5($k)." = {name: '{$k}', alink: '".$alink."'};".PHP_EOL;
    }
    $html .= '    </script>'.PHP_EOL;
    return $html;
}

/**
 * 获取所有菜单规则
 * @author liupan
 * @return array
 */
function all_auth_rule()
{
    static $cache;
    if($cache != null)
    {
        return $cache;
    }
    $ruleModel = model('admin/AuthRule');
    $cache = $ruleModel->getList(['owner' => 'system'], 'sort desc')->toArray();
    $cache = \app\admin\util\ArrayToolkit::index($cache, 'id');
    //todo 用thinkphp的缓存缓存一下
    return $cache;
}

/**
 * 获取当前用户拥有的菜单规则
 * @author liupan
 * @return array
 */
function user_rules()
{
    static $cache;
    if($cache != null)
    {
        return $cache;
    }
    $request = request();
    $user = $request->currentUser;
    $allRules = all_auth_rule();
    $userRuleIds = explode(',', $user['rules']);
    $userRules = [];
    foreach ($userRuleIds as $v)
    {
        if(isset($allRules[$v]) && $allRules[$v]['status'] == 'normal')
        {
            $userRules[$v] = trim(strtolower($allRules[$v]['name']));
        }
    }
    $cache = $userRules;
    return $cache;
}

/**
 * 获取指定rule节点的所有父级id
 * @param $ruleId int
 * @param $allRules array
 * @author liupan
 * @return array
 */
function find_rule_parents($ruleId, $allRules)
{
    if(!isset($allRules[$ruleId]))
    {
        return [];
    }
    $parents = [];
    $rule = $allRules[$ruleId];
    if($rule['pid'] > 0)
    {
        $parents[] = $rule['pid'];
    }
    return array_merge($parents, find_rule_parents($rule['pid'], $allRules));
}


/**
 * 根据当前rule节点
 * @author liupan
 * @return array
 */
function find_current_rule()
{
    static $cache;
    if($cache != null)
    {
        return $cache;
    }
    $request = request();
    $name = $request->controllerName.'/'.$request->actionName;
    $allRules = all_auth_rule();
    $rule = [];
    foreach ($allRules as $k => $v)
    {
        if(trim(strtolower($v['name'])) == trim(strtolower($name)))
        {
            $rule = $v;
            break;
        }
    }
    $cache = $rule;
    return $cache;
}



/**
 * 统一api接口的分页数据输出格式
 * @param $page int 当前分页
 * @param $limit int 每一页显示数量
 * @param $count int 总记录数
 * @param $list array 查询的数据
 * @return array
 */
function api_pagination($page, $limit,  $count, $list)
{
    $totalPage = ceil($count/$limit);
    $data = [];
    $data['curPage'] = $page;
    $data['limit'] = $limit;
    $data['totalPage'] = $totalPage;
    $data['count'] = $count;

    if($data['curPage'] < $data['totalPage'])
    {
        $data['nextPage'] = $data['curPage'] + 1;
    }
    if($data['curPage'] > 1)
    {
        $data['lastPage'] = $data['curPage'] - 1;
    }
    $data['data'] = $list;
    return $data;
}



/**
 * 排序分类树
 * @author liupan
 * @param $data
 * @param $parid
 * @param int $lev
 * @param string $id_index
 * @param string $pid_index
 * @return array
 */
function array_sort ($data, $parid, $lev = 0,$id_index = 'id', $pid_index = 'pid'){
    static $list;
    if($lev == 0) $list = [];
    foreach ($data as $v){
        if($v[$pid_index] == $parid){
            $v['lev'] = $lev;
            $list[] = $v;
            array_sort($data, $v[$id_index],$lev + 1, $id_index, $pid_index);
        }
    }
    return $list;
}

/**
 * 重组数据上下级关系
 * @author liupan
 * @param $data
 * @param int $pid
 * @param string $id_index
 * @param string $pid_index
 * @return mixed
 */
function array_tree($data, $pid = 0,$id_index = 'id', $pid_index = 'pid')
{
    $newArr = $data;
    foreach ($data as $k => $v)
    {
        if($v[$pid_index] == $pid){
            $data[$k]['child'] = array_tree($newArr, $v[$id_index], $id_index, $pid_index);
        }else{
            unset($data[$k]);
        }
    }
    return $data;
}


/**
 * 获取子级所有id
 * @author liupan
 * @param $data
 * @param int $pid
 * @param int $lev
 * @param string $idIndex
 * @param string $pidIndex
 * @return mixed
 */
function get_child_ids($data, $pid, $lev = 0, $idIndex = 'id', $pidIndex = 'pid')
{
    static $list;
    if($lev == 0) $list = [];
    foreach ($data as $v){
        if($v[$pidIndex] == $pid){
            $list[] = $v[$idIndex];
            get_child_ids($data, $v[$idIndex],$lev + 1, $idIndex, $pidIndex);
        }
    }
    return $list;
}