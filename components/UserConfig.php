<?php

namespace app\webvimark\modules\UserManagement\components;


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

		$session->set('__userRoles', AuthHelper::getRolesAsArray($identity->id));

		$session->set('__userRolesWithChildren', AuthHelper::getRolesAsArray($identity->id, true));

		$session->set('__userPermissions', AuthHelper::getPermissionsAsArray($identity->id));

		$session->set('__userRoutes', AuthHelper::getCalculatedRoutesAsArray($identity->id));
	}
} 