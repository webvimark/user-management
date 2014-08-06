<?php

namespace app\webvimark\modules\UserManagement;

class UserManagementModule extends \yii\base\Module
{
	/**
	 * Permission that will be assigned automatically for everyone, so you can assign
	 * routes like "site/index" to this permission and those routes will be available for everyone
	 *
	 * Basically it's permission for guests (and of course for everyone else)
	 *
	 * @var string
	 */
	public $commonPermissionName = 'commonPermission';

	/**
	 * After how many seconds confirmation token will be invalid
	 *
	 * @var int
	 */
	public $confirmationTokenExpire = 3600; // 1 hour

	/**
	 * Users CRUD
	 *
	 * @var string
	 */
	public $userControllerLayout = '@app/views/layouts/back.php';

	/**
	 * Role CRUD
	 *
	 * @var string
	 */
	public $rbacLayout = '@app/views/layouts/back.php';

	/**
	 * Login, logout, change password, etc
	 *
	 * @var string
	 */
	public $authControllerLayout = '@app/views/layouts/main.php';



	public $controllerNamespace = 'app\webvimark\modules\UserManagement\controllers';

	public function init()
	{
		parent::init();

		// custom initialization code goes here
	}
}
