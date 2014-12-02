<?php

use yii\db\Migration;

class m141201_220516_add_email_and_email_confirmed_to_user extends Migration
{
	public function safeUp()
	{
		$this->addColumn('user', 'email', 'varchar(128)');
		$this->addColumn('user', 'email_confirmed', 'tinyint(1) not null default 0');
		Yii::$app->cache->flush();

	}

	public function safeDown()
	{
		$this->dropColumn('user', 'email');
		$this->dropColumn('user', 'email_confirmed');
		Yii::$app->cache->flush();
	}
}
