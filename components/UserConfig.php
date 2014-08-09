<?php

namespace app\webvimark\modules\UserManagement\components;


use app\webvimark\modules\UserManagement\models\rbacDB\Permission;
use app\webvimark\modules\UserManagement\models\rbacDB\Role;
use app\webvimark\modules\UserManagement\models\rbacDB\Route;
use yii\web\User;
use Yii;

/**
 * Class UserConfig
 * @package app\webvimark\modules\UserManagement\components
 */
class UserConfig extends User
{
	/**
	 * @inheritdoc
	 */
	public $identityClass = 'app\webvimark\modules\UserManagement\models\User';

	/**
	 * @inheritdoc
	 */
	public $enableAutoLogin = true;

	/**
	 * @inheritdoc
	 */
	public $loginUrl = ['/user-management/auth/login'];

	/**
	 * Allows to call Yii::$app->user->isSuperadmin
	 *
	 * @return bool
	 */
	public function getIsSuperadmin()
	{
		return @Yii::$app->user->identity->superadmin == 1;
	}

	/**
	 * @return string
	 */
	public function getUsername()
	{
		return @Yii::$app->user->identity->username;
	}

	/**
	 * @inheritdoc
	 */
	protected function afterLogin($identity, $cookieBased, $duration)
	{
		$this->storeDataInSession($identity);

		parent::afterLogin($identity, $cookieBased, $duration);
	}

	/**
	 * Store accesses and some user details in session
	 *
	 * @param UserIdentity $identity
	 */
	protected function storeDataInSession($identity)
	{
		$session = Yii::$app->session;

		$session->set('__userRoles', array_keys(Role::getUserRoles($identity->id)));

		$session->set('__userRolesWithChildren', array_keys(Role::getUserRoles($identity->id, true)));

		$session->set('__userPermissions', array_keys(Permission::getUserPermissions($identity->id)));

		$session->set('__userRoutes', Route::getUserRoutes($identity->id));
	}
} 