<?php

use yii\db\Schema;
use yii\db\Migration;
use webvimark\modules\UserManagement\models\User;

class m140809_072112_insert_superadmin_to_user extends Migration
{
	public function safeUp()
	{
		$user = new User();
		$user->superadmin = 1;
		$user->status = User::STATUS_ACTIVE;
		$user->username = 'superadmin';
		$user->password = 'superadmin';
		$user->save(false);
	}

	public function safeDown()
	{
		$user = User::findByUsername('superadmin');

		if ( $user )
		{
			$user->delete();
		}
	}
}
