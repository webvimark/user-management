<?php

use yii\db\Schema;

class m140611_133903_init_rbac extends \yii\db\Migration
{

	public function up()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
		}

		$this->createTable('auth_rule', [
			'name' => Schema::TYPE_STRING . '(64) NOT NULL',
			'data' => Schema::TYPE_TEXT,
			'created_at' => Schema::TYPE_INTEGER,
			'updated_at' => Schema::TYPE_INTEGER,
			'PRIMARY KEY (name)',
		], $tableOptions);

		$this->createTable('auth_item', [
			'name' => Schema::TYPE_STRING . '(64) NOT NULL',
			'type' => Schema::TYPE_INTEGER . ' NOT NULL',
			'description' => Schema::TYPE_TEXT,
			'rule_name' => Schema::TYPE_STRING . '(64)',
			'data' => Schema::TYPE_TEXT,
			'created_at' => Schema::TYPE_INTEGER,
			'updated_at' => Schema::TYPE_INTEGER,
			'PRIMARY KEY (name)',
			'FOREIGN KEY (rule_name) REFERENCES ' . 'auth_rule' . ' (name) ON DELETE SET NULL ON UPDATE CASCADE',
		], $tableOptions);
		$this->createIndex('idx-auth_item-type', 'auth_item', 'type');

		$this->createTable('auth_item_child', [
			'parent' => Schema::TYPE_STRING . '(64) NOT NULL',
			'child' => Schema::TYPE_STRING . '(64) NOT NULL',
			'PRIMARY KEY (parent, child)',
			'FOREIGN KEY (parent) REFERENCES ' . 'auth_item' . ' (name) ON DELETE CASCADE ON UPDATE CASCADE',
			'FOREIGN KEY (child) REFERENCES ' . 'auth_item' . ' (name) ON DELETE CASCADE ON UPDATE CASCADE',
		], $tableOptions);

		$this->createTable('auth_assignment', [
			'item_name' => Schema::TYPE_STRING . '(64) NOT NULL',
			'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER,
			'PRIMARY KEY (item_name, user_id)',
			'FOREIGN KEY (item_name) REFERENCES ' . 'auth_item' . ' (name) ON DELETE CASCADE ON UPDATE CASCADE',
			'FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE',
		], $tableOptions);
	}

	public function down()
	{
		$this->dropTable('auth_assignment');
		$this->dropTable('auth_item_child');
		$this->dropTable('auth_item');
		$this->dropTable('auth_rule');
	}
}
