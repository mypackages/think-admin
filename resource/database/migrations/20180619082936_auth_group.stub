<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AuthGroup extends Migrator
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
        $table = $this->table('auth_group',['engine'=>'innodb']);
        $table->addColumn('pid', 'integer',['limit' => 11,'default'=>'0','comment'=>'父级id'])
            ->addColumn('name', 'string',['limit' => 32,'default'=> '','comment'=>'名称'])
            ->addColumn('rules', 'text',['default'=> null,'comment'=>'规则id'])
            ->addColumn('status', 'enum', ['values' => ['normal','locked'],'default'=>'normal' , 'comment' => '状态', 'null' => false])
            ->addColumn('create_time', 'integer',['limit' => 11,'null' => true,'default'=> null,'comment'=>'创建时间'])
            ->addColumn('update_time', 'integer',['limit' => 11,'null' => true,'default'=> null,'comment'=>'更新时间'])
            ->addColumn('delete_time', 'integer',['limit' => 11,'null' => false, 'default'=> 0,'comment'=>'软删除时间'])
            ->addIndex(['status'])
            ->create();
        $model = model('admin/AuthGroup');
        $data = [];
        $data['pid'] = '0';
        $data['name'] = '超级管理员';
        $data['rules'] = "*";
        $data['status'] = "normal";
        $user['delete_time'] = '0';
        $model->save($data);
    }

    public function down()
    {
        parent::down();
        $this->dropTable('auth_group');
    }
}
