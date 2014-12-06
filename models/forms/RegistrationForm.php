<?php
namespace webvimark\modules\UserManagement\models\forms;

use webvimark\modules\UserManagement\models\User;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\base\Model;
use Yii;

class RegistrationForm extends Model
{
	public $username;
	public $password;
	public $repeat_password;
	public $captcha;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			['captcha', 'captcha', 'captchaAction'=>'/user-management/auth/captcha'],

			[['username', 'password', 'repeat_password', 'captcha'], 'required'],
			[['username', 'password', 'repeat_password'], 'trim'],

			['username', 'match', 'pattern'=>Yii::$app->getModule('user-management')->registrationRegexp],
			['username', 'match', 'not'=>true, 'pattern'=>Yii::$app->getModule('user-management')->registrationBlackRegexp],

			['!username', 'exist',
				'targetClass'     => 'webvimark\modules\UserManagement\models\User',
				'targetAttribute' => 'username',
			],

			['username', 'string', 'max' => 50],
			['password', 'string', 'max' => 255],

			['repeat_password', 'compare', 'compareAttribute'=>'password'],
		];
	}

	public function attributeLabels()
	{
		return [
			'username'        => UserManagementModule::t('front', 'Login'),
			'password'        => UserManagementModule::t('front', 'Password'),
			'repeat_password' => UserManagementModule::t('front', 'Repeat password'),
			'captcha'         => UserManagementModule::t('front', 'Captcha'),
		];
	}

	/**
	 * @param bool $performValidation
	 *
	 * @return bool|User
	 */
	public function registerUser($performValidation = true)
	{
		if ( $performValidation AND !$this->validate() )
		{
			return false;
		}

		$user = new User();
		$user->username = $this->username;
		$user->password = $this->password;

		if ( $user->save() )
		{
			return $user;
		}
		else
		{
			$this->addError('username', UserManagementModule::t('front', 'Login has been taken'));
		}
	}
}
