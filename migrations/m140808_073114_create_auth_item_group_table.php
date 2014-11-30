<?php

use yii\db\Migration;

class m140808_073114_create_auth_item_group_table extends Migration
{
	public function safeUp()
	{
		$tableOptions = null;
		if ( $this->db->driverName === 'mysql' )
		{
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
		}

		$this->createTable('auth_item_group', [
			'code' => 'varchar(64) NOT NULL',
			'name' => 'varchar(255) NOT NULL',

			'created_at' => 'int',
			'updated_at' => 'int',
			'PRIMARY KEY (code)',

		], $tableOptions);

		$this->addColumn('auth_item', 'group_code', 'varchar(64)');
		$this->addForeignKey('fk_auth_item_group_code', 'auth_item', 'group_code', 'auth_item_group', 'code', 'SET NULL', 'CASCADE');

		Yii::$app->cache->flush();

	}

	public function safeDown()
	{
		$this->dropForeignKey('fk_auth_item_group_code', 'auth_item');
		$this->dropColumn('auth_item', 'group_code');

		$this->dropTable('auth_item_group');

		Yii::$app->cache->flush();
	}
}
