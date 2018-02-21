<?php

use yii\db\Migration;

class m141121_194858_split_browser_and_os_column extends Migration
{
	public function safeUp()
	{
		$this->addColumn(Yii::$app->getModule('user-management')->user_visit_log_table, 'browser', 'varchar(30)');
		$this->addColumn(Yii::$app->getModule('user-management')->user_visit_log_table, 'os', 'varchar(20)');
        if (Yii::$app->cache) {
			Yii::$app->cache->flush();
		}
	}

	public function safeDown()
	{
		$this->dropColumn(Yii::$app->getModule('user-management')->user_visit_log_table, 'os');
		$this->dropColumn(Yii::$app->getModule('user-management')->user_visit_log_table, 'browser');
        if (Yii::$app->cache) {
			Yii::$app->cache->flush();
		}
	}
}
