<?php

namespace webvimark\modules\UserManagement\controllers;

use webvimark\components\BaseController;
use webvimark\modules\UserManagement\models\ChangeOwnPasswordForm;
use webvimark\modules\UserManagement\models\LoginForm;
use webvimark\modules\UserManagement\models\PasswordRecoveryForm;
use webvimark\modules\UserManagement\models\RegistrationForm;
use webvimark\modules\UserManagement\models\User;
use Yii;
use yii\web\ForbiddenHttpException;

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
	 * Set layout from config
	 *
	 * @inheritdoc
	 */
	public function beforeAction($action)
	{
		if ( parent::beforeAction($action) )
		{
			$layouts = @$this->module->layouts[$this->id];

			if ( isset($layouts[$action->id]) )
			{
				$this->layout = $layouts[$action->id];
			}
			elseif ( isset($layouts['*']) )
			{
				$this->layout = $layouts['*'];
			}

			return true;
		}

		return false;
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
			$user = $model->registerUser(false);

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

		return $this->renderIsAjax('registration', compact('model'));
	}

	/**
	 * Form to recover password
	 *
	 * @return string|\yii\web\Response
	 */
	public function actionPasswordRecovery()
	{
		if ( !Yii::$app->user->isGuest )
		{
			$this->goHome();
		}

		$model = new PasswordRecoveryForm();

		if ( $model->load(Yii::$app->request->post()) AND $model->sendEmail() )
		{
			// TODO send mail
			return $this->renderIsAjax('passwordRecoverySuccess');
		}

		return $this->renderIsAjax('passwordRecovery', compact('model'));
	}
}
