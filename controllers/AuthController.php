<?php

namespace webvimark\modules\UserManagement\controllers;

use webvimark\components\BaseController;
use webvimark\modules\UserManagement\components\UserAuthEvent;
use webvimark\modules\UserManagement\models\forms\ChangeOwnPasswordForm;
use webvimark\modules\UserManagement\models\forms\LoginForm;
use webvimark\modules\UserManagement\models\forms\PasswordRecoveryForm;
use webvimark\modules\UserManagement\models\forms\RegistrationForm;
use webvimark\modules\UserManagement\models\User;
use webvimark\modules\UserManagement\UserManagementModule;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class AuthController extends BaseController
{
	/**
	 * @var array
	 */
	public $freeAccessActions = ['login', 'logout'];

	/**
	 * @return array
	 */
	public function actions()
	{
		return [
			'captcha' => [
				'class' => 'yii\captcha\CaptchaAction',
				'minLength'=>3,
				'maxLength'=>4,
				'offset'=>5
			],
		];
	}

	/**
	 * Login form
	 *
	 * @return string
	 */
	public function actionLogin()
	{
		if ( !Yii::$app->user->isGuest )
		{
			$this->goHome();
		}
		
		$model = new LoginForm();

		if ( $model->load(Yii::$app->request->post()) AND $model->login() )
		{
			$this->goBack();
		}

		return $this->renderIsAjax('login', compact('model'));
	}

	/**
	 * Logout and redirect to home page
	 */
	public function actionLogout()
	{
		Yii::$app->user->logout();

		return $this->redirect(Yii::$app->homeUrl);
	}

	/**
	 * Change your own password
	 *
	 * @throws \yii\web\ForbiddenHttpException
	 * @return string|\yii\web\Response
	 */
	public function actionChangeOwnPassword()
	{
		if ( Yii::$app->user->isGuest )
		{
			$this->goHome();
		}

		$user = User::getCurrentUser();

		if ( $user->status != User::STATUS_ACTIVE )
		{
			throw new ForbiddenHttpException();
		}

		$model = new ChangeOwnPasswordForm(['user'=>$user]);

		if ( $model->load(Yii::$app->request->post()) AND $model->changePassword() )
		{
			return $this->renderIsAjax('changeOwnPasswordSuccess');
		}

		return $this->renderIsAjax('changeOwnPassword', compact('model'));
	}

	/**
	 * Registration logic
	 *
	 * @return string
	 */
	public function actionRegistration()
	{
		if ( !Yii::$app->user->isGuest )
		{
			$this->goHome();
		}

		$model = new RegistrationForm();

		if ( $model->load(Yii::$app->request->post()) AND $model->validate() )
		{
			// Trigger event "before registration" and checks if it's valid
			if ( $this->triggerModuleEvent(UserAuthEvent::BEFORE_REGISTRATION, ['model'=>$model]) )
			{
				$user = $model->registerUser(false);

				// Trigger event "after registration" and checks if it's valid
				if ( $this->triggerModuleEvent(UserAuthEvent::AFTER_REGISTRATION, ['model'=>$model, 'user'=>$user]) )
				{
					if ( $user )
					{
						$roles = (array)$this->module->rolesAfterRegistration;

						foreach ($roles as $role)
						{
							User::assignRole($user->id, $role);
						}

						Yii::$app->user->login($user);

						return $this->redirect(Yii::$app->user->returnUrl);
					}
				}
			}

		}

		return $this->renderIsAjax('registration', compact('model'));
	}

	/**
	 * Form to recover password
	 *
	 * @return string|\yii\web\Response
	 */
	public function actionPasswordRecoveryRequest()
	{
		if ( !Yii::$app->user->isGuest )
		{
			$this->goHome();
		}

		$model = new PasswordRecoveryForm();

		if ( $model->load(Yii::$app->request->post()) AND $model->validate() )
		{
			if ( $this->triggerModuleEvent(UserAuthEvent::BEFORE_PASSWORD_RECOVERY_REQUEST, ['model'=>$model]) )
			{
				if ( $model->sendEmail(false) )
				{
					if ( $this->triggerModuleEvent(UserAuthEvent::AFTER_PASSWORD_RECOVERY_REQUEST, ['model'=>$model]) )
					{
						return $this->renderIsAjax('passwordRecoverySuccess');
					}
				}
				else
				{
					Yii::$app->session->setFlash('error', UserManagementModule::t('front', "Unable to send message for email provided"));
				}
			}
		}

		return $this->renderIsAjax('passwordRecovery', compact('model'));
	}

	/**
	 * Form to recover password
	 *
	 * @param string $token
	 *
	 * @throws \yii\web\NotFoundHttpException
	 * @return string|\yii\web\Response
	 */
	public function actionPasswordRecoveryChange($token)
	{
		if ( !Yii::$app->user->isGuest )
		{
			$this->goHome();
		}

		$user = User::findByConfirmationToken($token);

		if ( !$user )
		{
			throw new NotFoundHttpException(UserManagementModule::t('front', 'Token not found. It may be expired. Try reset password once more'));
		}

		$model = new ChangeOwnPasswordForm([
			'scenario'=>'restoreViaEmail',
			'user'=>$user,
		]);

		if ( $model->load(Yii::$app->request->post()) AND $model->validate() )
		{
			if ( $this->triggerModuleEvent(UserAuthEvent::BEFORE_PASSWORD_RECOVERY_COMPLETE, ['model'=>$model]) )
			{
				$model->changePassword();

				if ( $this->triggerModuleEvent(UserAuthEvent::AFTER_PASSWORD_RECOVERY_COMPLETE, ['model'=>$model]) )
				{
					return $this->renderIsAjax('changeOwnPasswordSuccess');
				}
			}
		}

		return $this->renderIsAjax('changeOwnPassword', compact('model'));
	}

	public function actionConfirmEmailRequest()
	{
		return $this->renderIsAjax('confirmEmailRequest');
	}

	/**
	 * Universal method for triggering events like "before registration", "after registration" and so on
	 *
	 * @param string $eventName
	 * @param array  $data
	 *
	 * @return bool
	 */
	protected function triggerModuleEvent($eventName, $data = [])
	{
		$event = new UserAuthEvent($data);

		$this->module->trigger($eventName, $event);

		return $event->isValid;
	}
}
