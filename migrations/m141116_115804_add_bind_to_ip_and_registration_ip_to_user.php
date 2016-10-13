<?php

use yii\db\Migration;

class m141116_115804_add_bind_to_ip_and_registration_ip_to_user extends Migration
{
	public function safeUp()
	{
		$this->addColumn(Yii::$app->getModule('user-management')->user_table, 'registration_ip', 'varchar(15)');
		$this->addColumn(Yii::$app->getModule('user-management')->user_table, 'bind_to_ip', 'string');

		if (Yii::$app->cache) {
			Yii::$app->cache->flush();
		}
	}

	public function safeDown()
	{
		$this->dropColumn(Yii::$app->getModule('user-management')->user_table, 'bind_to_ip');
		$this->dropColumn(Yii::$app->getModule('user-management')->user_table, 'registration_ip');

		if (Yii::$app->cache) {
			Yii::$app->cache->flush();
		}
	}
}
