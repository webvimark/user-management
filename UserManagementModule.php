<?php

namespace webvimark\modules\UserManagement;

use Yii;

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

	public $controllerNamespace = 'webvimark\modules\UserManagement\controllers';

	public function init()
	{
		parent::init();

		$this->registerTranslations();
	}

	public function registerTranslations()
	{
		Yii::$app->i18n->translations['modules/user-management/*'] = [
			'class'          => 'yii\i18n\PhpMessageSource',
			'sourceLanguage' => 'en',
			'basePath'       => '@vendor/webvimark/module-user-management/messages',
			'fileMap'        => [
				'modules/user-management/back' => 'back.php',
				'modules/user-management/front' => 'front.php',
			],
		];
	}

	public static function t($category, $message, $params = [], $language = null)
	{
		return Yii::t('modules/user-management/' . $category, $message, $params, $language);
	}
}
