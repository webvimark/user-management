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
		
	        // Check if auth_item_group_table Table exist
	        $table_name = \Yii::$app->getModule('user-management')->auth_item_group_table;
	        if (\Yii::$app->db->schema->getTableSchema($table_name) === null)
	        {
			// Create auth_item_group_table table
			$this->createTable($table_name, [
				'code' => 'varchar(64) NOT NULL',
				'name' => 'varchar(255) NOT NULL',
	
				'created_at' => 'int',
				'updated_at' => 'int',
				'PRIMARY KEY (code)',
	
			], $tableOptions);
	
			$this->addColumn(Yii::$app->getModule('user-management')->auth_item_table, 'group_code', 'varchar(64)');
			$this->addForeignKey('fk_auth_item_group_code', Yii::$app->getModule('user-management')->auth_item_table, 'group_code', Yii::$app->getModule('user-management')->auth_item_group_table, 'code', 'SET NULL', 'CASCADE');
	        }

		if (Yii::$app->cache) {
			Yii::$app->cache->flush();
		}
	}

	public function safeDown()
	{
		$this->dropForeignKey('fk_auth_item_group_code', Yii::$app->getModule('user-management')->auth_item_table);
		$this->dropColumn(Yii::$app->getModule('user-management')->auth_item_table, 'group_code');

		$this->dropTable(Yii::$app->getModule('user-management')->auth_item_group_table);

		if (Yii::$app->cache) {
			Yii::$app->cache->flush();
		}
	}
}
