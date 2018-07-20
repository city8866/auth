<?php

use Phinx\Migration\AbstractMigration;

class SysSms extends AbstractMigration
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
    public function change()
    {
        // create the table
        $table = $this->table('sys_sms',array('engine'=>'MyISAM'));
        $table->addColumn('code', 'string',array('limit' => 6,'default'=>'','comment'=>'验证码'))
            ->addColumn('mobile', 'string',array('limit' => 14,'default'=>'','comment'=>'手机号'))
            ->addColumn('type', 'integer',array('default'=>0,'comment'=>'类型'))
            ->addColumn('status', 'integer',array('default'=>0,'comment'=>'状态'))
            ->addColumn('created_at', 'integer',array('default'=>0,'comment'=>'创建时间'))
            ->addIndex(array('id'), array('unique' => true))
            ->create();
    }
}
