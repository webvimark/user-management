<?php
namespace app\webvimark\modules\UserManagement\models\rbacDB;

use Exception;
use Yii;

class Permission extends AbstractItem
{
	const ITEM_TYPE = self::TYPE_PERMISSION;

	/**
	 * Assign route to permission and create them if they don't exists
	 * Helper mainly for migrations
	 *
	 * @param string $permissionName
	 * @param string $routeName
	 * @param null   $permissionDescription
	 *
	 * @throws \InvalidArgumentException
	 * @return true|static|string
	 */
	public static function assignRoute($permissionName, $routeName, $permissionDescription = null)
	{
		$route = Route::findOne(['name' => $routeName]);

		if ( !$route )
		{
			throw new \InvalidArgumentException("Route {$routeName} not found");
		}

		$permission = static::findOne(['name' => $permissionName]);

		if ( !$permission )
		{
			$permission = static::create($permissionName, $permissionDescription);

			if ( $permission->hasErrors() )
			{
				return $permission;
			}
		}

		try
		{
			Yii::$app->db->createCommand()
				->insert('auth_item_child', [
					'parent' => $permission->name,
					'child'  => $route->name,
				])->execute();
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}


		return true;
	}
}