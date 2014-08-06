<?php
namespace app\webvimark\modules\UserManagement\models\rbacDB;

use Exception;
use Yii;

class Role extends AbstractItem
{
	const ITEM_TYPE = self::TYPE_ROLE;

	/**
	 * Assign route to role via permission and create them if they don't exists
	 * Helper mainly for migrations
	 *
	 * @param string $roleName
	 * @param string $permissionName
	 * @param string $routeName
	 * @param null   $roleDescription
	 * @param null   $permissionDescription
	 *
	 * @throws \InvalidArgumentException
	 * @return true|static|string
	 */
	public static function assignRouteViaPermission($roleName, $permissionName, $routeName, $roleDescription = null, $permissionDescription = null)
	{
		$role = static::findOne(['name' => $roleName]);

		if ( !$role )
		{
			throw new \InvalidArgumentException("Role with name = {$roleName} not found");
		}

		$route = Route::findOne(['name' => $routeName]);

		if ( !$route )
		{
			throw new \InvalidArgumentException("Route {$routeName} not found");
		}

		$permission = Permission::findOne(['name' => $permissionName]);

		if ( !$permission )
		{
			$permission = Permission::create($permissionName, $permissionDescription);

			if ( $permission->hasErrors() )
			{
				return $permission;
			}
		}

		try
		{
			Yii::$app->db->createCommand()
				->insert('auth_item_child', [
					'parent' => $role->name,
					'child'  => $permission->name,
				])->execute();

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