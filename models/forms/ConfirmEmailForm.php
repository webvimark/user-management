<?php
namespace webvimark\modules\UserManagement\models\forms;

use webvimark\modules\UserManagement\models\User;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\base\Model;
use Yii;

class ConfirmEmailForm extends Model
{
	/**
	 * @var User
	 */
	public $user;

	/**
	 * @var string
	 */
	public $email;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [

			['email', 'required'],
			['email', 'trim'],
			['email', 'email'],

			['email', 'validateEmailConfirmedUnique'],
		];
	}

	/**
	 * Check that there is no such confirmed E-mail in the system
	 */
	public function validateEmailConfirmedUnique()
	{
		if ( $this->email )
		{
			$exists = User::findOne([
				'email'=>$this->email,
				'email_confirmed'=>1,
			]);

			if ( $exists )
			{
				$this->addError('email', UserManagementModule::t('front', 'This E-mail already exists'));
			}
		}
	}

	/**
	 * @return array
	 */
	public function attributeLabels()
	{
		return [
			'email' => 'E-mail',
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

		//$this->user->generateConfirmationToken();

		return Yii::$app->mailer->compose('emailConfirmation', ['user' => $this->user])
			->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
			->setTo($this->email)
			->setSubject(UserManagementModule::t('front', 'E-mail confirmation for') . ' ' . Yii::$app->name)
			->send();
	}
}
