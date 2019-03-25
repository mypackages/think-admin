<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AuthRule extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up()
    {
        parent::up();
        $table = $this->table('auth_rule',['engine'=>'innodb']);
        $table->addColumn('pid', 'integer',['limit' => 11,'default'=>'0', 'comment'=>'父级id'])
            ->addColumn('child_ids', 'text',['default'=> null,'comment'=>'子集id'])
            ->addColumn('name', 'string',['limit' => 100,'default'=>'', 'null' => false,'comment'=>'规则名称'])
            ->addColumn('title', 'string',['limit' => 50,'default'=>'', 'null' => false,'comment'=>'规则标题'])
            ->addColumn('icon', 'string',['limit' => 50,'default'=>'', 'null' => false,'comment'=>'图标'])
            ->addColumn('condition', 'string',['limit' => 255,'default'=>'', 'null' => false,'comment'=>'条件'])
            ->addColumn('remark', 'string',['limit' => 50,'default'=>'', 'null' => false,'comment'=>'备注'])
            ->addColumn('is_menu', 'integer',['limit' => 2,'default'=>'0', 'null' => false,'comment'=>'是否是菜单'])
            ->addColumn('status', 'enum', ['values' => ['normal','hidden'],'default'=>'hidden' , 'comment' => '状态', 'null' => false])
            ->addColumn('menu_right', 'string',['limit' => 255,'default'=>'', 'null' => false,'comment'=>'菜单右侧显示的内容'])
            ->addColumn('sort', 'integer',['limit' => 6,'default'=>'0', 'null' => false,'comment'=>'排序权重'])
            ->addColumn('create_time', 'integer',['limit' => 11,'null' => true,'default'=> null,'comment'=>'创建时间'])
            ->addColumn('update_time', 'integer',['limit' => 11,'null' => true,'default'=> null,'comment'=>'更新时间'])
            ->addColumn('delete_time', 'integer',['limit' => 11,'null' => false, 'default'=> 0,'comment'=>'软删除时间'])
            ->addIndex(['name', 'delete_time'], ['unique' => true])
            ->addIndex(['pid'])
            ->addIndex(['status'])
            ->create();
        $model = model('admin/AuthRule');
        $list = [
            ['id' => '1', 'pid'=>'0', 'child_ids' => '439,440,441,442,443,444,445,446,455,456,460', 'name' => 'index/index', 'title' => '控制台', 'icon' => 'fa fa-dashboard', 'is_menu' => 1, 'status' => 'normal', 'sort' => 100, 'menu_right' => '<small class="label pull-right bg-blue">hot</small>'],
            ['id' => '2', 'pid'=>'6', 'child_ids' => '3,48,49,50,4,9,28,51,5,21,22,23', 'name' => 'auth', 'title' => '权限管理', 'icon' => 'fa fa-group', 'is_menu' => 1, 'status' => 'normal', 'sort' => 99],
            ['id' => '3', 'pid'=>'2', 'child_ids' => '48,49,50', 'name' => 'auth.admin/index', 'title' => '管理员管理', 'icon' => 'fa fa-user', 'is_menu' => 1, 'status' => 'normal', 'sort' => 100],
            ['id' => '4', 'pid'=>'2', 'child_ids' => '9,28,51', 'name' => 'auth.group/index', 'title' => '角色组', 'icon' => 'fa fa-group', 'is_menu' => 1, 'status' => 'normal', 'sort' => 99],
            ['id' => '5', 'pid'=>'2', 'child_ids' => '21,22,23', 'name' => 'auth.rule/index', 'title' => '菜单规则', 'icon' => 'fa fa-bars', 'is_menu' => 1, 'status' => 'normal', 'sort' => 98],
            ['id' => '6', 'pid'=>'0', 'child_ids' => '2,3,48,49,50,4,9,28,51,5,21,22,23', 'name' => 'system', 'title' => '系统设置', 'icon' => 'fa fa-cogs', 'is_menu' => 1, 'status' => 'normal', 'sort' => 100, 'menu_right' => ''],
            ['id' => '9', 'pid'=>'4', 'child_ids' => '', 'name' => 'auth.group/add', 'title' => '添加', 'icon' => 'fa-circle-o', 'is_menu' => 0, 'status' => 'normal', 'sort' => 99],
            ['id' => '21', 'pid'=>'5', 'child_ids' => '', 'name' => 'auth.rule/add', 'title' => '添加', 'icon' => 'fa-circle-o', 'is_menu' => 0, 'status' => 'normal', 'sort' => 99],
            ['id' => '22', 'pid'=>'5', 'child_ids' => '', 'name' => 'auth.rule/edit', 'title' => '修改', 'icon' => 'fa-circle-o', 'is_menu' => 0, 'status' => 'normal', 'sort' => 99],
            ['id' => '23', 'pid'=>'5', 'child_ids' => '', 'name' => 'auth.rule/delete', 'title' => '删除', 'icon' => 'fa-circle-o', 'is_menu' => 0, 'status' => 'normal', 'sort' => 99],
            ['id' => '28', 'pid'=>'4', 'child_ids' => '', 'name' => 'auth.group/edit', 'title' => '修改', 'icon' => 'fa-circle-o', 'is_menu' => 0, 'status' => 'normal', 'sort' => 99],
            ['id' => '41', 'pid'=>'0', 'child_ids' => '52,53', 'name' => 'file', 'title' => '文件管理', 'icon' => 'fa-file', 'is_menu' => 0, 'status' => 'normal', 'sort' => 61],
            ['id' => '48', 'pid'=>'3', 'child_ids' => '', 'name' => 'auth.admin/add', 'title' => '添加', 'icon' => 'fa-circle-o', 'is_menu' => 0, 'status' => 'normal', 'sort' => 99],
            ['id' => '49', 'pid'=>'3', 'child_ids' => '', 'name' => 'auth.admin/edit', 'title' => '修改', 'icon' => 'fa-circle-o', 'is_menu' => 0, 'status' => 'normal', 'sort' => 99],
            ['id' => '50', 'pid'=>'3', 'child_ids' => '', 'name' => 'auth.admin/delete', 'title' => '删除', 'icon' => 'fa-circle-o', 'is_menu' => 0, 'status' => 'normal', 'sort' => 99],
            ['id' => '51', 'pid'=>'4', 'child_ids' => '', 'name' => 'auth.group/delete', 'title' => '删除', 'icon' => 'fa-circle-o', 'is_menu' => 0, 'status' => 'normal', 'sort' => 99],
            ['id' => '52', 'pid'=>'41', 'child_ids' => '', 'name' => 'file/deleteFile', 'title' => '删除', 'icon' => 'fa-circle-o', 'is_menu' => 0, 'status' => 'normal', 'sort' => 99],
            ['id' => '53', 'pid'=>'41', 'child_ids' => '', 'name' => 'file/upload', 'title' => '上传', 'icon' => 'fa-circle-o', 'is_menu' => 0, 'status' => 'normal', 'sort' => 99],

        ];
        foreach ($list as $v)
        {
            $model->insert($v);
        }
    }

    public function down()
    {
        parent::down();
        $this->dropTable('auth_rule');
    }
}
