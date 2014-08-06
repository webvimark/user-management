<?php
namespace app\webvimark\modules\UserManagement\components;

use app\webvimark\modules\UserManagement\models\User;
use yii\base\Action;
use yii\base\ActionFilter;
use Yii;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;

class AccessFilter extends ActionFilter
{
	/**
	 * Check if user has access to current route
	 *
	 * @param Action $action the action to be executed.
	 *
	 * @return boolean whether the action should continue to be executed.
	 */
	public function beforeAction($action)
	{
		if ( $this->isFreeAccess($action) )
		{
			return true;
		}

		if ( Yii::$app->user->isGuest )
		{
			if ( User::canRoute(Yii::$app->requestedRoute) )
			{
				return true;
			}
			else
			{
				$this->denyAccess();
			}
		}

		// If user has been deleted, then destroy session and redirect to home page
		if ( Yii::$app->user->identity === null )
		{
			Yii::$app->getSession()->destroy();

			Yii::$app->getResponse()->redirect(Yii::$app->getHomeUrl());
		}

		// Superadmin owns everyone
		if (Yii::$app->user->isSuperadmin )
		{
			return true;
		}

		if ( Yii::$app->user->identity->status != User::STATUS_ACTIVE)
		{
			Yii::$app->user->logout();
			Yii::$app->getResponse()->redirect(Yii::$app->getHomeUrl());
		}

		if ( User::canRoute(Yii::$app->requestedRoute) )
		{
			return true;
		}

		$this->denyAccess();
	}

	/**
	 * Check if current route allowed for guest
	 *
	 * @param Action $action
	 *
	 * @return bool
	 */
	protected function isGuestAllowed($action)
	{
		return false;
	}

	/**
	 * Check if controller has $freeAccess = true or $action in $freeAccessActions
	 * Or it's login, logout, error page
	 *
	 * @param Action $action
	 *
	 * @return bool
	 */
	protected function isFreeAccess($action)
	{
		$controller = $action->controller;

		if ( $controller->hasProperty('freeAccess') AND $controller->freeAccess === true )
		{
			return true;
		}

		if ( $controller->hasProperty('freeAccessActions') AND isset($controller->freeAccessActions[$action->id]) )
		{
			return true;
		}

		$systemPages = [
			Url::to(Yii::$app->user->loginUrl),
			Url::to(['/user-management/auth/logout']),
			Url::to(Yii::$app->errorHandler->errorAction),
		];

		if ( in_array(Url::to(Yii::$app->requestedRoute), $systemPages) )
		{
			return true;
		}

		return false;
	}

	/**
	 * Denies the access of the user.
	 * The default implementation will redirect the user to the login page if he is a guest;
	 * if the user is already logged, a 403 HTTP exception will be thrown.
	 *
	 * @throws ForbiddenHttpException if the user is already logged in.
	 */
	protected function denyAccess()
	{
		if ( Yii::$app->user->getIsGuest() )
		{
			Yii::$app->user->loginRequired();
		}
		else
		{
			throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
		}
	}
} 