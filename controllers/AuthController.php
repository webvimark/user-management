<?php

namespace webvimark\modules\UserManagement\controllers;

use webvimark\components\BaseController;
use webvimark\modules\UserManagement\models\LoginForm;
use webvimark\modules\UserManagement\models\User;
use webvimark\modules\UserManagement\UserManagementModule;
use Yii;
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
			$layouts = $this->module->layouts[$this->id];

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
	 * Change tour own password
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
			Yii::$app->session->setFlash('success', UserManagementModule::t('back', 'Password has been changed'));

			$model->password = $model->current_password = $model->repeat_password = null;

			if ( !Yii::$app->request->isAjax )
			{
				$this->refresh();

				Yii::$app->end();
			}
		}

		return $this->renderIsAjax('changeOwnPassword', compact('model'));
	}
}
