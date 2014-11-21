<?php

use yii\db\Migration;

class m141121_194858_split_browser_and_os_column extends Migration
{
	public function safeUp()
	{
		$this->addColumn('user_visit_log', 'browser', 'varchar(30)');
		$this->addColumn('user_visit_log', 'os', 'varchar(20)');
		$this->renameColumn('user_visit_log', 'browser_and_os', 'user_agent');
		Yii::$app->cache->flush();

	}

	public function safeDown()
	{
		$this->dropColumn('user_visit_log', 'os');
		$this->dropColumn('user_visit_log', 'browser');
		$this->renameColumn('user_visit_log', 'user_agent', 'browser_and_os');

		Yii::$app->cache->flush();
	}
}
