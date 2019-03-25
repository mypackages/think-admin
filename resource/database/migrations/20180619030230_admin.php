<?php

use think\Db;
use think\migration\Migrator;
use think\migration\db\Column;

class Admin extends Migrator
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
        $table = $this->table('admin',['engine'=>'innodb']);
        $table->addColumn('uuid', 'string',['limit' => 64, 'null' => false, 'default'=> '','comment'=>'唯一id'])
            ->addColumn('username', 'string',['limit' => 20, 'null' => false, 'default'=> '','comment'=>'用户名'])
            ->addColumn('nickname', 'string',['limit' => 50, 'null' => false, 'default'=> '','comment'=>'昵称'])
            ->addColumn('password', 'string',['limit' => 32, 'null' => false,'default'=>'','comment'=>'密码'])
            ->addColumn('salt', 'string',['limit' => 30, 'null' => false,'default'=>'','comment'=>'密码盐'])
            ->addColumn('email', 'string',['limit' => 100, 'null' => false,'default'=>'','comment'=>'邮箱'])
            ->addColumn('status', 'enum', ['values' => ['normal','locked'],'default'=>'normal' , 'comment' => '状态', 'null' => false])
            ->addColumn('mobile', 'string',['limit' => 15, 'null' => false,'default'=>'','comment'=>'手机号'])
            ->addColumn('wechat', 'string',['limit' => 50,'null' => false,'default'=>'','comment'=>'微信号'])
            ->addColumn('qq', 'string',['limit' => 20,'null' => false,'default'=>'','comment'=>'qq号'])
            ->addColumn('remember_me', 'string',['limit' => 64,'null' => false,'default'=>'','comment'=>'记住密码的token'])
            ->addColumn('remember_deadline', 'integer',['limit' => 11,'null' => true, 'default'=> null,'comment'=>'记住密码的过期时间'])
            ->addColumn('login_time', 'integer',['limit' => 11,'null' => true, 'default'=> null,'comment'=>'登录时间'])
            ->addColumn('create_time', 'integer',['limit' => 11,'null' => true, 'default'=> null,'comment'=>'创建时间'])
            ->addColumn('update_time', 'integer',['limit' => 11,'null' => true, 'default'=> null,'comment'=>'更新时间'])
            ->addColumn('delete_time', 'integer',['limit' => 11,'null' => false, 'default'=> 0,'comment'=>'软删除时间'])
            ->addIndex(['username', 'delete_time'], ['unique' => true])
            ->addIndex(['uuid', 'delete_time'], ['unique' => true])
            ->addIndex(['remember_me', 'delete_time'], ['unique' => true])
            ->addIndex(['status'])
            ->addIndex(['username'])
            ->create();
        $adminModel = model('admin/Admin');
        $user = [];
        $user['username'] = 'admin';
        $user['nickname'] = '超级管理员';
        $user['uuid'] = \app\admin\util\StringToolkit::keyGen();
        $user['salt'] = \app\admin\util\StringToolkit::randString(6);
        $user['password'] = $adminModel->encryptPassword('123456', $user['salt']);
        $user['delete_time'] = '0';
        $adminModel->save($user);
    }

    public function down()
    {
        parent::down();
        $this->dropTable('admin');
    }

}
