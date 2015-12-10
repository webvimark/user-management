<?php

namespace webvimark\modules\UserManagement\components;

use webvimark\modules\UserManagement\models\rbacDB\AbstractItem;
use webvimark\modules\UserManagement\models\rbacDB\Permission;
use webvimark\modules\UserManagement\models\rbacDB\Role;
use webvimark\modules\UserManagement\models\rbacDB\Route;
use Yii;
use yii\base\InvalidParamException;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\rbac\DbManager;

class AuthHelper
{
	const SESSION_PREFIX_LAST_UPDATE         = '__auth_last_update';
	const SESSION_PREFIX_ROLES               = '__userRoles';
	const SESSION_PREFIX_PERMISSIONS         = '__userPermissions';
	const SESSION_PREFIX_ROUTES              = '__userRoutes';


	/**
	 * Example how to handle layouts from config file
	 *
	 * 'on beforeAction'=>['webvimark\modules\UserManagement\components\AuthHelper', 'layoutHandler'],
	 *
	 * @param \yii\base\ActionEvent $event
	 */
	public static function layoutHandler($event)
	{
		if ( $event->action->uniqueId == 'user-management/auth/login' )
		{
			$event->action->controller->layout = 'loginLayout.php';
		}
		elseif ( $event->action->controller->id == 'auth' )
		{
			if ( in_array($event->action->id, ['change-own-password', 'confirm-email']) )
			{
				$event->action->controller->layout = '//back.php';
			}
			else
			{
				$event->action->controller->layout = '//main.php';
			}
		}
		else
		{
			$event->action->controller->layout = '//back.php';
		}
	}

	/**
	 * Gather all user permissions and roles and store them in the session
	 *
	 * @param UserIdentity $identity
	 */
	public static function updatePermissions($identity)
	{
		$session = Yii::$app->session;

		// Clear data first in case we want to refresh permissions
		$session->remove(self::SESSION_PREFIX_ROLES);
		$session->remove(self::SESSION_PREFIX_PERMISSIONS);
		$session->remove(self::SESSION_PREFIX_ROUTES);

		// Set permissions last mod time
		$session->set(self::SESSION_PREFIX_LAST_UPDATE, filemtime(self::getPermissionsLastModFile()));

		// Save roles, permissions and routes in session
		$session->set(self::SESSION_PREFIX_ROLES, array_keys(Role::getUserRoles($identity->id)));
		$session->set(self::SESSION_PREFIX_PERMISSIONS, array_keys(Permission::getUserPermissions($identity->id)));
		$session->set(self::SESSION_PREFIX_ROUTES, Route::getUserRoutes($identity->id));
	}

	/**
	 * Checks if permissions has been changed somehow, and refresh data in session if necessary
	 */
	public static function ensurePermissionsUpToDate()
	{
		if ( !Yii::$app->user->isGuest )
		{
			if ( Yii::$app->session->get(self::SESSION_PREFIX_LAST_UPDATE) != filemtime(self::getPermissionsLastModFile()) )
			{
				static::updatePermissions(Yii::$app->user->identity);
			}
		}
	}

	/**
	 * Get path to file that store time of the last auth changes
	 *
	 * @return string
	 */
	public static function getPermissionsLastModFile()
	{
		$file = Yii::$app->runtimePath . '/__permissions_last_mod.txt';

		if ( !is_file($file) )
		{
			file_put_contents($file, '');
			chmod($file, 0777);
		}

		return $file;
	}

	/**
	 * Change modification time of permissions last mod file
	 */
	public static function invalidatePermissions()
	{
		touch(static::getPermissionsLastModFile());
	}
	
	/**
	 * Return route without baseUrl and start it with slash
	 *
	 * @param string|array $route
	 *
	 * @return string
	 */
	public static function unifyRoute($route)
	{
		// If its like Html::a('Create', ['create'])
		if ( is_array($route) AND strpos($route[0], '/') === false )
		{
			$route = Url::toRoute($route);
		}

		if ( Yii::$app->getUrlManager()->showScriptName === true )
		{
			$baseUrl = Yii::$app->getRequest()->scriptUrl;
		}
		else
		{
			$baseUrl = Yii::$app->getRequest()->baseUrl;
		}

		// Check if $route has been passed as array or as string with params (or without)
		if ( !is_array($route) )
		{
			$route = explode('?', $route);
		}

		$routeAsString = $route[0];

		// If it's not clean url like localhost/folder/index.php/bla-bla then remove
		// baseUrl and leave only relative path 'bla-bla'
		if ( $baseUrl )
		{
			if ( strpos($routeAsString, $baseUrl) === 0 )
			{
				$routeAsString = substr_replace($routeAsString, '', 0, strlen($baseUrl));
			}
		}

		$languagePrefix = '/' . Yii::$app->language . '/';

		// Remove language prefix
		if ( strpos($routeAsString, $languagePrefix) === 0 )
		{
			$routeAsString = substr_replace($routeAsString, '', 0, strlen($languagePrefix));
		}

		return '/' . ltrim($routeAsString, '/');
	}

	/**
	 * Get child routes, permissions or roles
	 *
	 * @param string $itemName
	 * @param integer $childType
	 *
	 * @return array
	 */
	public static function getChildrenByType($itemName, $childType)
	{
		$dbManager = Yii::$app->authManager instanceof DbManager ? Yii::$app->authManager : new DbManager();

		$children = $dbManager->getChildren($itemName);

		$result = [];

		foreach ($children as $id => $item)
		{
			if ( $item->type == $childType )
			{
				$result[$id] = $item;
			}
		}

		return $result;
	}

	/**
	 * Select items that has "/" in permissions
	 *
	 * @param array $allPermissions
	 *
	 * @return object
	 */
	public static function separateRoutesAndPermissions($allPermissions)
	{
		$arrayOfPermissions = $allPermissions;

		$routes = [];
		$permissions = [];

		foreach ($arrayOfPermissions as $id => $item)
		{
			if ( $item->type == AbstractItem::TYPE_ROUTE )
			{
				$routes[$id] = $item;

			}
			else
			{
				$permissions[$id] = $item;

			}
		}

		return (object)compact('routes', 'permissions');
	}


	/**
	 * @return array
	 */
	public static function getAllModules()
	{
		$result = [];

		$currentEnvModules = \Yii::$app->getModules();

		foreach ($currentEnvModules as $moduleId => $uselessStuff)
		{
			$result[$moduleId] = \Yii::$app->getModule($moduleId);
		}

		return $result;
	}


	// ================= Credits to mdm/admin module =================
	/**
	 * @return array
	 */
	public static function getRoutes()
	{
		$result = [];
		self::getRouteRecursive(Yii::$app, $result);

		return array_reverse(array_combine($result, $result));
	}

	/**
	 * @param \yii\base\Module $module
	 * @param array            $result
	 */
	private static function getRouteRecursive($module, &$result)
	{
		foreach ($module->getModules() as $id => $child)
		{
			if ( ($child = $module->getModule($id)) !== null )
			{
				self::getRouteRecursive($child, $result);
			}
		}
		/* @var $controller \yii\base\Controller */
		foreach ($module->controllerMap as $id => $value)
		{
			$controller = Yii::createObject($value, [
				$id,
				$module
			]);
			self::getActionRoutes($controller, $result);
			$result[] = '/' . $controller->uniqueId . '/*';
		}

		$namespace = trim($module->controllerNamespace, '\\') . '\\';
		self::getControllerRoutes($module, $namespace, '', $result);

		if ( $module->uniqueId )
		{
			$result[] = '/'. $module->uniqueId . '/*';
		}
		else
		{
			$result[] = $module->uniqueId . '/*';
		}
	}

	/**
	 * @param \yii\base\Controller $controller
	 * @param Array                $result all controller action.
	 */
	private static function getActionRoutes($controller, &$result)
	{
		$prefix = '/' . $controller->uniqueId . '/';
		foreach ($controller->actions() as $id => $value)
		{
			$result[] = $prefix . $id;
		}
		$class = new \ReflectionClass($controller);
		foreach ($class->getMethods() as $method)
		{
			$name = $method->getName();
			if ( $method->isPublic() && !$method->isStatic() && strpos($name, 'action') === 0 && $name !== 'actions' )
			{
				$result[] = $prefix . Inflector::camel2id(substr($name, 6));
			}
		}
	}

	/**
	 * @param \yii\base\Module $module
	 * @param $namespace
	 * @param $prefix
	 * @param $result
	 */
	private static function getControllerRoutes($module, $namespace, $prefix, &$result)
	{
		try
		{
			$path = Yii::getAlias('@' . str_replace('\\', '/', $namespace));
		}
		catch (InvalidParamException $e)
		{
			$path = $module->getBasePath() . '/controllers';
		}

		if ( is_dir($path) )
		{
			foreach (scandir($path) as $file)
			{
				if ( strpos($file, '.') === 0 )
				{
					continue;
				}

				if ( is_dir($path . '/' . $file) )
				{
					self::getControllerRoutes($module, $namespace . $file . '\\', $prefix . $file . '/', $result);
				}
				elseif ( strcmp(substr($file, -14), 'Controller.php') === 0 )
				{
					$id = Inflector::camel2id(substr(basename($file), 0, -14), '-', true);
					$className = $namespace . Inflector::id2camel($id) . 'Controller';
					if ( strpos($className, '-') === false && class_exists($className) && is_subclass_of($className, 'yii\base\Controller') )
					{
						$controller = new $className($prefix . $id, $module);
						self::getActionRoutes($controller, $result);
						$result[] = '/' . $controller->uniqueId . '/*';
					}
				}
			}
		}
	}
} 