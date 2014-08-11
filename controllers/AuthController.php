<?php

namespace webvimark\modules\UserManagement\controllers;

use webvimark\modules\UserManagement\components\AccessController;
use webvimark\modules\UserManagement\models\LoginForm;
use Yii;

class AuthController extends AccessController
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
	 * Set layout
	 *
	 * @inheritdoc
	 */
	public function beforeAction($action)
	{
		$this->layout = $this->module->authControllerLayout;

		return parent::beforeAction($action);
	}
}
