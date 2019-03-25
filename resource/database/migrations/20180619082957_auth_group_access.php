<?php

use think\Db;
use think\migration\Migrator;
use think\migration\db\Column;

class AuthGroupAccess extends Migrator
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
        $table = $this->table('auth_group_access',['engine'=>'innodb']);
        $table->setId(false)
            ->addColumn('user_id', 'integer',['limit' => 11,'default'=>'0','comment'=>'用户id'])
            ->addColumn('group_id', 'integer',['limit' => 11,'default'=>'0','comment'=>'用户组id'])
            ->addIndex(['user_id', 'group_id'], ['unique' => true])
            ->addIndex(['user_id'])
            ->addIndex(['group_id'])
            ->create();
        Db::name('auth_group_access')->data(['group_id' => 1, 'user_id' => 1])->insert();
    }

    public function down()
    {
        parent::down();
        $this->dropTable('auth_group_access');
    }
}
