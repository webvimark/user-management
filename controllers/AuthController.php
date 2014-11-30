<?php

namespace webvimark\modules\UserManagement\controllers;

use webvimark\components\BaseController;
use webvimark\modules\UserManagement\models\LoginForm;
use webvimark\modules\UserManagement\models\User;
use Yii;
use yii\base\DynamicModel;
use yii\web\ForbiddenHttpException;

class AuthController extends BaseController
{
	public $freeAccessActions = ['login', 'logout'];

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

		$this->redirect(Yii::$app->homeUrl);
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

		$model = User::getCurrentUser();
		$model->scenario = 'changeOwnPassword';

		if ( $model->status != User::STATUS_ACTIVE )
		{
			throw new ForbiddenHttpException();
		}

		if ( $model->load(Yii::$app->request->post()) AND $model->save() )
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

		$model = new User(['scenario'=>'registration']);

		if ( $model->load(Yii::$app->request->post()) AND $model->save() )
		{
			$roles = (array)$this->module->rolesAfterRegistration;

			foreach ($roles as $role)
			{
				User::assignRole($model->id, $role);
			}

			Yii::$app->user->login($model);

			$this->redirect(Yii::$app->user->returnUrl);
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

		$model = (new DynamicModel(['email']))->addRule(['email'], 'required')
			->addRule('email', 'email')
			->addRule('email', 'exist', [
				'targetClass'     => 'webvimark\modules\UserManagement\models\User',
				'targetAttribute' => 'email',
			]);

		if ( $model->load(Yii::$app->request->post()) AND $model->validate() )
		{
			// TODO send mail
			return $this->renderIsAjax('passwordRecoverySuccess');
		}

		return $this->renderIsAjax('passwordRecovery', compact('model'));
	}
}
