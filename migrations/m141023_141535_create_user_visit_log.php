<?php

use yii\db\Migration;

class m141023_141535_create_user_visit_log extends Migration
{
	public function safeUp()
	{
		$tableOptions = null;
		if ( $this->db->driverName === 'mysql' )
		{
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
		}

		$this->createTable(Yii::$app->getModule('user-management')->user_visit_log_table, array(
			'id'             => 'pk',
			'token'          => 'string not null',
			'ip'             => 'varchar(15) not null',
			'language'       => 'char(2) not null',
			'browser_and_os' => 'string not null',
			'user_id'        => 'int',
			'visit_time'     => 'int not null',
			0                => 'FOREIGN KEY (user_id) REFERENCES '.Yii::$app->getModule('user-management')->user_table.' (id) ON DELETE SET NULL ON UPDATE CASCADE',
		), $tableOptions);


	}

	public function safeDown()
	{
		$this->dropTable(Yii::$app->getModule('user-management')->user_visit_log_table);

	}
}
