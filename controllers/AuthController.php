<?php

namespace webvimark\modules\UserManagement\controllers;

use webvimark\components\BaseController;
use webvimark\modules\UserManagement\models\LoginForm;
use webvimark\modules\UserManagement\models\User;
use Yii;

class AuthController extends BaseController
{
	public $freeAccess = true;

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
		
		$this->layout = 'loginLayout';

		$model = new LoginForm();

		if ( $model->load(Yii::$app->request->post()) AND $model->login() )
		{
			$this->goBack();
		}

		if ( Yii::$app->request->isAjax )
			return $this->renderAjax('login', compact('model'));
		else
			return $this->render('login', compact('model'));
	}

	/**
	 * Logout and redirect to home page
	 */
	public function actionLogout()
	{
		Yii::$app->user->logout();
		$this->redirect(Yii::$app->homeUrl);
	}

	public function actionChangePassword()
	{
		return $this->render('changePassword');
	}

	/**
	 * Registration logic
	 *
	 * @return string
	 */
	public function actionRegister()
	{
		$model = new User(['scenario'=>'register']);

		if ( $model->load(Yii::$app->request->post()) AND $model->save() )
		{
			Yii::$app->user->login($model);

			$this->redirect(Yii::$app->user->returnUrl);
		}

		return $this->renderIsAjax('register', compact('model'));
	}
}
