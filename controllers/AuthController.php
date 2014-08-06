<?php

namespace app\webvimark\modules\UserManagement\controllers;

use app\webvimark\modules\UserManagement\components\AccessController;
use app\webvimark\modules\UserManagement\models\LoginForm;
use Yii;

class AuthController extends AccessController
{
	/**
	 * Login form
	 *
	 * @return string
	 */
	public function actionLogin()
	{
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
