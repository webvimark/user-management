<?php
namespace webvimark\modules\UserManagement\models\forms;

use webvimark\modules\UserManagement\models\User;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\base\Model;
use Yii;

class PasswordRecoveryForm extends Model
{
	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @var string
	 */
	public $email;

	/**
	 * @var string
	 */
	public $captcha;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			['captcha', 'captcha', 'captchaAction'=>'/user-management/auth/captcha'],

			[['email', 'captcha'], 'required'],
			['email', 'trim'],
			['email', 'email'],

			['email', 'validateEmailConfirmedAndUserActive'],
		];
	}

	/**
	 * @return bool
	 */
	public function validateEmailConfirmedAndUserActive()
	{
		if ( !Yii::$app->getModule('user-management')->checkAttempts() )
		{
			$this->addError('email', UserManagementModule::t('front', 'Too many attempts'));

			return false;
		}

		$user = User::findOne([
			'email'           => $this->email,
			'email_confirmed' => 1,
			'status'          => User::STATUS_ACTIVE,
		]);

		if ( $user )
		{
			$this->user = $user;
		}
		else
		{
			$this->addError('email', UserManagementModule::t('front', 'E-mail is invalid'));
		}
	}

	/**
	 * @return array
	 */
	public function attributeLabels()
	{
		return [
			'email' => 'E-mail',
			'captcha' => UserManagementModule::t('front', 'Captcha'),
		];
	}

	/**
	 * @param bool $performValidation
	 *
	 * @return bool
	 */
	public function sendEmail($performValidation = true)
	{
		if ( $performValidation AND !$this->validate() )
		{
			return false;
		}

		$this->user->generateConfirmationToken();
		$this->user->save(false);

		return Yii::$app->mailer->compose(Yii::$app->getModule('user-management')->mailerOptions['passwordRecoveryFormViewFile'], ['user' => $this->user])
			->setFrom(Yii::$app->getModule('user-management')->mailerOptions['from'])
			->setTo($this->email)
			->setSubject(UserManagementModule::t('front', 'Password reset for') . ' ' . Yii::$app->name)
			->send();
	}
}
