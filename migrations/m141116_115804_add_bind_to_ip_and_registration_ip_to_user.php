<?php

use yii\db\Migration;

class m141116_115804_add_bind_to_ip_and_registration_ip_to_user extends Migration
{
	public function safeUp()
	{
		$this->addColumn('user', 'registration_ip', 'varchar(15)');
		$this->addColumn('user', 'bind_to_ip', 'string');
		Yii::$app->cache->flush();

	}

	public function safeDown()
	{
		$this->dropColumn('user', 'bind_to_ip');
		$this->dropColumn('user', 'registration_ip');
		Yii::$app->cache->flush();
	}
}
