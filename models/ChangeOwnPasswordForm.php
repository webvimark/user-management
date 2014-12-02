<?php
namespace webvimark\modules\UserManagement\models;

use webvimark\helpers\LittleBigHelper;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\base\Model;
use Yii;

class ChangeOwnPasswordForm extends Model
{
	/**
	 * @var User
	 */
	public $user;

	/**
	 * @var string
	 */
	public $current_password;
	/**
	 * @var string
	 */
	public $password;
	/**
	 * @var string
	 */
	public $repeat_password;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['password', 'repeat_password'], 'required'],
			[['password', 'repeat_password', 'current_password'], 'string', 'max'=>255],
			[['password', 'repeat_password', 'current_password'], 'trim'],

			['repeat_password', 'compare', 'compareAttribute'=>'password'],

			['current_password', 'required', 'except'=>'restoreViaEmail'],
			['current_password', 'validateCurrentPassword', 'except'=>'restoreViaEmail'],
		];
	}

	public function attributeLabels()
	{
		return [
			'current_password' => UserManagementModule::t('back', 'Current password'),
			'password'         => UserManagementModule::t('front', 'Password'),
			'repeat_password'  => UserManagementModule::t('front', 'Repeat password'),
		];
	}


	/**
	 * Validates current password
	 */
	public function validateCurrentPassword()
	{
		if ( !Yii::$app->getModule('user-management')->checkAttempts() )
		{
			$this->addError('current_password', UserManagementModule::t('back', 'Too many attempts'));

			return false;
		}

		if ( !Yii::$app->security->validatePassword($this->current_password, $this->user->password_hash) )
		{
			$this->addError('current_password', UserManagementModule::t('back', "Wrong password"));
		}
	}

	/**
	 * @return bool
	 */
	public function changePassword()
	{
		if ( $this->validate() )
		{
			$this->user->password = $this->password;
			return $this->user->save();
		}

		return false;
	}
}
