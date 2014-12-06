<?php
namespace webvimark\modules\UserManagement\components;

use webvimark\modules\UserManagement\models\forms\ChangeOwnPasswordForm;
use webvimark\modules\UserManagement\models\forms\PasswordRecoveryForm;
use webvimark\modules\UserManagement\models\forms\RegistrationForm;
use webvimark\modules\UserManagement\models\User;
use yii\base\Event;

class UserAuthEvent extends Event
{
	const BEFORE_REGISTRATION = 'beforeRegistration';
	const AFTER_REGISTRATION = 'afterRegistration';

	const BEFORE_PASSWORD_RECOVERY_REQUEST = 'beforePasswordRecoveryRequest';
	const AFTER_PASSWORD_RECOVERY_REQUEST = 'afterPasswordRecoveryRequest';

	const BEFORE_PASSWORD_RECOVERY_COMPLETE = 'beforePasswordRecoveryComplete';
	const AFTER_PASSWORD_RECOVERY_COMPLETE = 'afterPasswordRecoveryComplete';

	/**
	 * @var User
	 */
	public $user;

	/**
	 * @var RegistrationForm|PasswordRecoveryForm|ChangeOwnPasswordForm
	 */
	public $model;

	/**
	 * Determine if script should continue after this event
	 *
	 * @var boolean
	 */
	public $isValid = true;
}