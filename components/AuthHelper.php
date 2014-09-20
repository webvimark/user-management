<?php

namespace webvimark\modules\UserManagement\components;


use Yii;
use yii\base\InvalidParamException;
use yii\helpers\Inflector;
use yii\helpers\Url;

class AuthHelper
{

	/**
	 * Return route without baseUrl and start it with slash
	 *
	 * @param string|array $route
	 *
	 * @return string
	 */
	public static function unifyRoute($route)
	{
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

		return '/' . ltrim($routeAsString, '/');
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
			if ( strpos($item->name, '/') !== false )
			{
				$routes[$id] = $item;
			}
			else
			{
				$permissions[$id] = $item;
			}
		}

//		sort($routes);

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
				$id = Inflector::camel2id(substr(basename($file), 0, -14));
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