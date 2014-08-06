<?php

use yii\db\Schema;
use yii\db\Migration;

class m140728_173539_create_user_table extends Migration
{
	public function safeUp()
	{
		$tableOptions = null;
		if ( $this->db->driverName === 'mysql' )
		{
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
		}

		$this->createTable('user', array(
			'id'                 => 'pk',
			'username'           => 'string not null',
			'auth_key'           => 'varchar(32) not null',
			'password_hash'      => 'string not null',
			'confirmation_token' => 'string',
			'status'             => 'int not null default 1',
			'superadmin'         => 'tinyint(1) default 0',
			'created_at'         => 'int not null',
			'updated_at'         => 'int not null',
		), $tableOptions);


	}

	public function safeDown()
	{
		$this->dropTable('user');

	}
}
