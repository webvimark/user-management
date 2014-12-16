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

		$this->createTable(Yii::$app->getModule('user-management')->auth_rule_table, [
			'name' => Schema::TYPE_STRING . '(64) NOT NULL',
			'data' => Schema::TYPE_TEXT,
			'created_at' => Schema::TYPE_INTEGER,
			'updated_at' => Schema::TYPE_INTEGER,
			'PRIMARY KEY (name)',
		], $tableOptions);

		$this->createTable(Yii::$app->getModule('user-management')->auth_item_table, [
			'name' => Schema::TYPE_STRING . '(64) NOT NULL',
			'type' => Schema::TYPE_INTEGER . ' NOT NULL',
			'description' => Schema::TYPE_TEXT,
			'rule_name' => Schema::TYPE_STRING . '(64)',
			'data' => Schema::TYPE_TEXT,
			'created_at' => Schema::TYPE_INTEGER,
			'updated_at' => Schema::TYPE_INTEGER,
			'PRIMARY KEY (name)',
			'FOREIGN KEY (rule_name) REFERENCES ' . Yii::$app->getModule('user-management')->auth_rule_table . ' (name) ON DELETE SET NULL ON UPDATE CASCADE',
		], $tableOptions);
		$this->createIndex('idx-auth_item-type', Yii::$app->getModule('user-management')->auth_item_table, 'type');

		$this->createTable(Yii::$app->getModule('user-management')->auth_item_child_table, [
			'parent' => Schema::TYPE_STRING . '(64) NOT NULL',
			'child' => Schema::TYPE_STRING . '(64) NOT NULL',
			'PRIMARY KEY (parent, child)',
			'FOREIGN KEY (parent) REFERENCES ' . Yii::$app->getModule('user-management')->auth_item_table . ' (name) ON DELETE CASCADE ON UPDATE CASCADE',
			'FOREIGN KEY (child) REFERENCES ' . Yii::$app->getModule('user-management')->auth_item_table . ' (name) ON DELETE CASCADE ON UPDATE CASCADE',
		], $tableOptions);

		$this->createTable(Yii::$app->getModule('user-management')->auth_assignment_table, [
			'item_name' => Schema::TYPE_STRING . '(64) NOT NULL',
			'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER,
			'PRIMARY KEY (item_name, user_id)',
			'FOREIGN KEY (item_name) REFERENCES ' . Yii::$app->getModule('user-management')->auth_item_table . ' (name) ON DELETE CASCADE ON UPDATE CASCADE',
			'FOREIGN KEY (user_id) REFERENCES '.Yii::$app->getModule('user-management')->user_table.' (id) ON DELETE CASCADE ON UPDATE CASCADE',
		], $tableOptions);
	}

	public function down()
	{
		$this->dropTable(Yii::$app->getModule('user-management')->auth_assignment_table);
		$this->dropTable(Yii::$app->getModule('user-management')->auth_item_child_table);
		$this->dropTable(Yii::$app->getModule('user-management')->auth_item_table);
		$this->dropTable(Yii::$app->getModule('user-management')->auth_rule_table);
	}
}
