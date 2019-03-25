<?php
/**
 * Created by PhpStorm.
 * User: liupan
 * Email: 498501258@qq.com
 * Date: 2018/6/17
 * Time: 下午8:14
 */
namespace app\admin\controller;
use app\admin\middleware\checkPermission;
use app\admin\traits\AjaxReturn;
use think\Controller;
use think\Exception;

class Base extends Controller
{
    use AjaxReturn;
    protected $noAuthAction = []; //不需要鉴权的方法名称写进这个数组里
    protected $model;
    protected $validateRule;
    protected $indexArraySort = false; //index方法的ajax请求数据是否需要重新排序
    protected $indexOrder = ''; //index方法的ajax请求数据模型排序方式
    protected $indexWith = ''; //index方法的ajax请求数据模型预加载参数
    protected $editWith = ''; //edit方法查询当前数据的预加载参数
    protected $indexHasPage = false; //index方法的ajax请求是否需要分页
    protected $indexFields = []; //index方法的ajax请求需要的字段
    protected $currentUser; //当前登录用户
    protected $middleware = [checkPermission::class];
    public function initialize()
    {
        $this->currentUser = $this->request->currentUser;
        $this->request->noAuthAction = array_merge($this->noAuthAction, ['editQuery']);
        if(!$this->request->isPost()) //post请求不需要赋值下面的变量
        {
            $this->assign('menu', menu_tree());       //获取左侧菜单
            $this->assign('position', get_position()['tree']);  //获取位置导航
            $this->assign('currentMenu', get_position()['current']); //获取当前菜单
            $this->assign('currentRule', find_current_rule()); //获取当前rule节点
        }
        $this->assign('currentUser', $this->currentUser);
        $this->assign('resDomain', '/static');
    }

    /**
     * 列表
     */
    public function index()
    {
        if($this->request->isPost())
        {
            $condition = input('post.');
            $page = isset($condition['page']) ? intval($condition['page']) : 0;
            $limit = isset($condition['limit']) ? intval($condition['limit']) : 0;
            if($this->indexHasPage) //需要分页的数据返回
            {
                $count = $this->model->getList($condition, $this->indexOrder, $this->indexWith, true, $this->indexFields);
                $list = $this->model->getList($condition, $this->indexOrder, $this->indexWith, false, $this->indexFields);
                $list = is_object($list) ? $list->toArray() : $list;
                $this->successReturn('请求成功', api_pagination($page, $limit, $count, $list), true);
            }
            //不需要分页的数据返回
            $list = $this->model->getList($condition, $this->indexOrder, $this->indexWith, false, $this->indexFields);
            $list = is_object($list) ? $list->toArray() : $list;
            $list = $this->indexArraySort ? array_sort($list, 0) : $list;
            $this->successReturn('请求成功', $list);
        }
        return $this->fetch();
    }


    /**
     * 新增
     */
    public function add()
    {
        if($this->request->isPost())
        {
            try{
                $param = input('post.');
                if (!$this->validateRule->scene('add')->check($param))
                {
                    throw new Exception($this->validateRule->getError());
                }
                $this->addHandle($param);
                $this->successReturn('添加成功');
            }catch (Exception $e)
            {
                $this->failReturn($e->getMessage());
            }
        }
        return $this->fetch();
    }


    /**
     * 修改
     */
    public function edit($id)
    {
        if($this->request->isPost())
        {
            try{
                $id = intval($id);
                $param = input('post.');
                $param['id'] = $id;
                if (!$this->validateRule->scene('edit')->check($param))
                {
                    throw new Exception($this->validateRule->getError());
                }
                $this->editHandle($param, $id);
                $this->successReturn('修改成功');
            }catch (Exception $e)
            {
                $this->failReturn($e->getMessage());
            }
        }
        $this->assign('id', $id);
        return $this->fetch();
    }

    /**
     * 删除
     */
    public function delete()
    {
        try{
            if(!$this->request->isPost())
            {
                throw new Exception('非法请求');
            }
            $ids = input('param.id', []);
            if(!is_array($ids)) //单个删除
            {
                $id = intval($ids);
                if($id <= 0)
                {
                    throw new Exception('id错误');
                }
                $ids = [$id]; //单个id也拼成数组形式
            }else{   //批量删除
                $ids = array_filter(array_map('intval', $ids));
                if(empty($ids))
                {
                    throw new Exception('请正确选择要删除的id');
                }
            }
            $this->deleteValidate($ids); //删除前验证
            $rzt = $this->model->destroy($ids);
            if($rzt <= 0)
            {
                throw new Exception('删除失败');
            }
            $this->successReturn('删除成功');
        }catch (Exception $e)
        {
            $this->failReturn($e->getMessage());
        }
    }
    /**
     * 删除数据时的验证, 在子类重写，因为不是每次都需要故没使用验证类
     */
    protected function deleteValidate($ids)
    {
        return true;
    }

    /**
     * 关联新增, 要在子控制器里面重写
     */
    protected function addHandle($data)
    {
        return $this->model->allowField(true)->save($data);
    }
    /**
     * 关联修改, 要在子控制器里面重写
     */
    protected function editHandle($param, $id)
    {
        return $this->model->allowField(true)->save($param, ['id' => $id]);
    }


    /**
     * 编辑的时候查询数据,根据需求重写
     */
    public function editQuery($id)
    {
        try{
            if(!app('adminAuth')->check('edit'))
            {
                throw new Exception('无权限访问');
            }
            if($this->editWith == '')
            {
                $data = $this->model->find($id);
            }else{
                $data = $this->model->with($this->editWith)->find($id);
            }
            $this->successReturn('请求成功', $data);
        }catch (Exception $e)
        {
            $this->failReturn($e->getMessage());
        }
    }

}
