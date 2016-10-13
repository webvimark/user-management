<?php
namespace webvimark\modules\UserManagement\models\rbacDB;

use webvimark\modules\UserManagement\components\AuthHelper;
use yii\base\Action;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use Yii;

class Route extends AbstractItem
{
	const ITEM_TYPE = self::TYPE_ROUTE;

	/**
	 * Get all routes available for this user
	 *
	 * @param int  $userId
	 * @param bool $withSubRoutes
	 *
	 * @return array
	 */
	public static function getUserRoutes($userId, $withSubRoutes = true)
	{
		$permissions = array_keys(Permission::getUserPermissions($userId));

		if ( !$permissions )
		{
			return [];
		}

		$auth_item = Yii::$app->getModule('user-management')->auth_item_table;
		$auth_item_child = Yii::$app->getModule('user-management')->auth_item_child_table;

		$routes = (new Query)
			->select(['name'])
			->from($auth_item)
			->innerJoin($auth_item_child, '('.$auth_item_child.'.child = '.$auth_item.'.name AND '.$auth_item.'.type = :type)')
			->params([
				':type'=>self::TYPE_ROUTE,
			])
			->where([
				$auth_item_child . '.parent' => $permissions,
			])
			->column();

		return $withSubRoutes ? static::withSubRoutes($routes, ArrayHelper::map(Route::find()->asArray()->all(), 'name', 'name')) : $routes;
	}

	/**
	 * Return given route with all they sub-routes
	 *
	 * @param array $givenRoutes
	 * @param array $allRoutes
	 *
	 * @return array
	 */
	public static function withSubRoutes($givenRoutes, $allRoutes)
	{
		$result = [];

		foreach ($allRoutes as $route)
		{
			foreach ($givenRoutes as $givenRoute)
			{
				if ( static::isSubRoute($givenRoute, $route) )
				{
					$result[] = $route;
				}
			}
		}

		return $result;
	}

	/**
	 * Checks if "candidate" is sub-route of "route". For example:
	 *
	 * "/module/controller/action" is sub-route of "/module/*"
	 *
	 * @param string $route
	 * @param string $candidate
	 *
	 * @return bool
	 */
	public static function isSubRoute($route, $candidate)
	{
		if ( $route == $candidate )
		{
			return true;
		}

		// If it's full access to module or controller
		if ( substr($route, -2) == '/*' )
		{
			$route = rtrim($route, '*');

			if ( strpos($candidate, $route) === 0 )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Refresh list of all routes from controllers, modules, etc.
	 *
	 * If $deleteUnusedRoutes is true, than all routes that are not longer exists in this application
	 * (for example if you delete some controller or module) will be deleted.
	 *
	 * $deleteUnusedRoutes = false is recommended for application with "advanced" structure, because frontend
	 * and backend have they own set of routes.
	 *
	 * @param bool $deleteUnusedRoutes
	 */
	public static function refreshRoutes($deleteUnusedRoutes = true)
	{
		$allRoutes = AuthHelper::getRoutes();

		$currentRoutes = ArrayHelper::map(Route::find()->asArray()->all(), 'name', 'name');

		$toAdd = array_diff(array_keys($allRoutes), array_keys($currentRoutes));

		foreach ($toAdd as $addItem)
		{
			Route::create($addItem);
		}

		$toRemove = false;
		if ( $deleteUnusedRoutes )
		{
			$toRemove = array_diff(array_keys($currentRoutes), array_keys($allRoutes));

			if ( $toRemove )
			{
				Route::deleteAll(['in', 'name', $toRemove]);
			}
		}


		if ( $toAdd || $toRemove )
		{
			if (Yii::$app->cache) {
				Yii::$app->cache->delete('__commonRoutes');
			}
		}
	}

	/**
	 * Checks if route is in array of allowed routes
	 *
	 * @param string $route
	 * @param array  $allowedRoutes
	 *
	 * @return boolean
	 */
	public static function isRouteAllowed($route, $allowedRoutes)
	{
		if ( in_array($route, $allowedRoutes) )
		{
			return true;
		}

		foreach ($allowedRoutes as $allowedRoute)
		{
			// If some controller fully allowed (wildcard)
			if (substr($allowedRoute, -1) == '*')
			{
				$routeArray = explode('/', $route);
				array_splice($routeArray, -1);

				$allowedRouteArray = explode('/', $allowedRoute);
				array_splice($allowedRouteArray, -1);

				if (array_diff($routeArray, $allowedRouteArray) === array())
					return true;
			}
		}

		return false;
	}


	/**
	 * Check if controller has $freeAccess = true or $action in $freeAccessActions
	 * Or it's login, logout, error page
	 *
	 * @param string $route
	 * @param Action|null $action
	 *
	 * @return bool
	 */
	public static function isFreeAccess($route, $action = null)
	{
		if ( $action )
		{
			$controller = $action->controller;

			if ( $controller->hasProperty('freeAccess') AND $controller->freeAccess === true )
			{
				return true;
			}

			if ( $controller->hasProperty('freeAccessActions') AND in_array($action->id, $controller->freeAccessActions) )
			{
				return true;
			}
		}

		$systemPages = [
			'/user-management/auth/logout',
			AuthHelper::unifyRoute(Yii::$app->errorHandler->errorAction),
			AuthHelper::unifyRoute(Yii::$app->user->loginUrl),
		];

		if ( in_array($route, $systemPages) )
		{
			return true;
		}

		// Registration can be enabled either by this option or by adding '/user-management/auth/registration' route to guest permissions
		if ( $route == '/user-management/auth/registration' && Yii::$app->getModule('user-management')->enableRegistration === true )
		{
			return true;
		}

		if ( static::isInCommonPermission($route) )
		{
			return true;
		}

		return false;
	}

	/**
	 * Check if current route allowed for everyone (in commonPermission routes)
	 *
	 * @param string $currentFullRoute
	 *
	 * @return bool
	 */
	protected static function isInCommonPermission($currentFullRoute)
	{
		$commonRoutes = Yii::$app->cache ? Yii::$app->cache->get('__commonRoutes') : false;

		if ( $commonRoutes === false )
		{
			$commonRoutesDB = (new Query())
				->select('child')
				->from(Yii::$app->getModule('user-management')->auth_item_child_table)
				->where(['parent'=>Yii::$app->getModule('user-management')->commonPermissionName])
				->column();

			$commonRoutes = Route::withSubRoutes($commonRoutesDB, ArrayHelper::map(Route::find()->asArray()->all(), 'name', 'name'));

			if ( Yii::$app->cache )
				Yii::$app->cache->set('__commonRoutes', $commonRoutes, 3600);
		}

		return in_array($currentFullRoute, $commonRoutes);
	}
}