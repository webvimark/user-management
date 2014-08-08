<?php

namespace app\webvimark\modules\UserManagement\components;

use app\webvimark\modules\UserManagement\models\rbacDB\Route;
use app\webvimark\modules\UserManagement\models\User;
use yii\base\Action;
use Yii;
use yii\base\Module;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\Controller;

class AccessController extends Controller
{
	/**
	 * Full url like '/site/index' instead of ''
	 *
	 * @var string
	 */
	public $currentFullRoute;

	/**
	 * @inheritdoc
	 */
	public function beforeAction($action)
	{
		$this->calculateFullRoute($action);

		if ( !$this->beforeControllerAction($action) )
		{
			return false;
		}

		return parent::beforeAction($action);
	}

	/**
	 * Make full url like '/site/index' instead of ''
	 *
	 * @param Action $action
	 */
	protected function calculateFullRoute($action)
	{
		$parts[] = $action->id;
		$parts[] = $action->controller->id;

		$fullParts = $this->prependModulesRecursive($action->controller->module, $parts);

		$this->currentFullRoute = '/' . implode('/', array_reverse($fullParts));
	}

	/**
	 * @param Module $module
	 * @param array $parts
	 *
	 * @return array
	 */
	protected function prependModulesRecursive($module, $parts)
	{
		if ( $module->module )
		{
			$parts[] = $module->id;

			return $this->prependModulesRecursive($module->module, $parts);
		}

		return $parts;
	}


	/**
	 * Check if user has access to current route
	 *
	 * @param Action $action the action to be executed.
	 *
	 * @return boolean whether the action should continue to be executed.
	 */
	public function beforeControllerAction($action)
	{
		if ( $this->isFreeAccess($action) )
		{
			return true;
		}

		if ( Yii::$app->user->isGuest )
		{
			if ( $this->isRouteAllowedForEveryOne() )
			{
				return true;
			}
			else
			{
				$this->denyAccess();
			}
		}

//		// If user has been deleted, then destroy session and redirect to home page
//		if ( Yii::$app->user->identity === null )
//		{
//			Yii::$app->getSession()->destroy();
//			$this->denyAccess();
//		}

		// Superadmin owns everyone
		if ( Yii::$app->user->isSuperadmin )
		{
			return true;
		}

		if ( Yii::$app->user->identity->status != User::STATUS_ACTIVE)
		{
			Yii::$app->user->logout();
			Yii::$app->getResponse()->redirect(Yii::$app->getHomeUrl());
		}

		if ( User::canRoute($this->currentFullRoute) )
		{
			return true;
		}

		$this->denyAccess();
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
			AuthHelper::unifyRoute(Yii::$app->user->loginUrl),
			AuthHelper::unifyRoute(['/user-management/auth/logout']),
			AuthHelper::unifyRoute(Yii::$app->errorHandler->errorAction),
		];

		if ( in_array($this->currentFullRoute, $systemPages) )
		{
			return true;
		}

		return false;
	}

	/**
	 * Check if current route allowed for everyone (in commonPermission routes)
	 *
	 * @return bool
	 */
	protected function isRouteAllowedForEveryOne()
	{
		$commonRoutes = Yii::$app->cache->get('__commonRoutes');

		if ( !$commonRoutes )
		{
			$commonRoutesDB = (new Query())
				->select('child')
				->from('auth_item_child')
				->where(['parent'=>Yii::$app->getModule('user-management')->commonPermissionName])
				->column();

			$commonRoutes = Route::withSubRoutes($commonRoutesDB, ArrayHelper::map(Route::find()->asArray()->all(), 'name', 'name'));

			Yii::$app->cache->set('__commonRoutes', $commonRoutes, 3600);
		}

		return in_array($this->currentFullRoute, $commonRoutes);
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