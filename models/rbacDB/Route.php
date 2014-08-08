<?php
namespace app\webvimark\modules\UserManagement\models\rbacDB;

class Route extends AbstractItem
{
	const ITEM_TYPE = self::TYPE_ROUTE;

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
}