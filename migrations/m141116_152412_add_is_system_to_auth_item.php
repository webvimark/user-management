<?php

use webvimark\modules\UserManagement\models\rbacDB\Permission;
use yii\db\Migration;

class m141116_152412_add_is_system_to_auth_item extends Migration
{
	public function safeUp()
	{
		$this->addColumn('auth_item', 'is_system', 'tinyint(1) not null default 0');
		Yii::$app->cache->flush();

		$commonPermission = Permission::findOne(['name'=>'commonPermission']);
		$commonPermission->is_system = 1;
		$commonPermission->save(false);
	}

	public function safeDown()
	{
		$this->dropColumn('auth_item', 'is_system');
		Yii::$app->cache->flush();
	}
}
