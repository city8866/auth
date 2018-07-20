<?php

use Phinx\Migration\AbstractMigration;

class NsUser extends AbstractMigration
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
        $table = $this->table('ns_user',array('engine'=>'MyISAM'));
        $table->addColumn('uid', 'string',array('limit' => 15,'default'=>'','comment'=>'用户id'))
            ->addColumn('auth_key', 'string',array('limit' => 64,'default'=>'','comment'=>'用户key'))
            ->addColumn('auth_secret', 'string',array('limit' => 64,'default'=>md5('123456'),'comment'=>'用户密钥'))
            ->addColumn('access_token', 'string',array('limit' => 64,'default'=>'','comment'=>'token'))
            ->addColumn('mobile', 'string',array('limit' => 14,'default'=>'','comment'=>'手机号'))
            ->addColumn('role', 'integer',array('default'=>0,'comment'=>'角色'))
            ->addColumn('status', 'integer',array('default'=>0,'comment'=>'状态'))
            ->addColumn('created_at', 'integer',array('default'=>0,'comment'=>'创建时间'))
            ->addColumn('updated_at', 'integer',array('default'=>0,'comment'=>'更新时间'))
            ->addColumn('expires_at', 'integer',array('default'=>0,'comment'=>'过期时间'))
            ->addIndex(array('id'), array('unique' => true))
            ->create();
    }
}
