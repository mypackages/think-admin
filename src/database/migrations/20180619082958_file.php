<?php

use think\migration\Migrator;
use think\migration\db\Column;

class File extends Migrator
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
        $table = $this->table('file',['engine'=>'innodb']);
        $table->addColumn('type', 'string',['limit' => 15,'default'=> '','comment'=>'内容类型'])
            ->addColumn('uuid', 'string',['limit' => 64, 'null' => false,'default'=>'','comment'=>'唯一id'])
            ->addColumn('path', 'string',['limit' => 255, 'null' => false,'default'=>'','comment'=>'文件路径，相对路径'])
            ->addColumn('url', 'string',['limit' => 255, 'null' => false,'default'=>'','comment'=>'对外访问地址'])
            ->addColumn('driver', 'string',['limit' => 20, 'null' => false,'default'=>'','comment'=>'存储驱动类型'])
            ->addColumn('size', 'string',['limit' => 20, 'null' => true,'default'=> null,'comment'=>'文件大小'])
            ->addColumn('name', 'string',['limit' => 255, 'null' => false,'default'=>'','comment'=>'文件名称'])
            ->addColumn('ext', 'string',['limit' => 10, 'null' => false,'default'=>'','comment'=>'后缀名'])
            ->addColumn('create_time', 'integer',['limit' => 11,'null' => true,'default'=> null,'comment'=>'创建时间'])
            ->addIndex(['uuid'])
            ->addIndex(['type', 'uuid'])
            ->create();
        $model = model('admin/AuthGroup');
        $data = [];
        $data['pid'] = '0';
        $data['name'] = '超级管理员';
        $data['rules'] = "*";
        $data['status'] = "normal";
        $model->save($data);
    }

    public function down()
    {
        parent::down();
        $this->dropTable('auth_group');
    }
}
