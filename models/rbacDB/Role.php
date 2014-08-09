<?php
namespace webvimark\modules\UserManagement\models\rbacDB;

use Exception;
use Yii;
use yii\rbac\DbManager;

class Role extends AbstractItem
{
	const ITEM_TYPE = self::TYPE_ROLE;

	/**
	 * @param int     $userId
	 * @param bool $withChildren
	 *
	 * @return array|\yii\rbac\Role[]
	 */
	public static function getUserRoles($userId, $withChildren = false)
	{
		return (new DbManager())->getRolesByUser($userId);
	}

	/**
	 * Assign route to role via permission and create permission if it don't exists
	 * Helper mainly for migrations
	 *
	 * @param string $roleName
	 * @param string $permissionName
	 * @param array $routes
	 * @param null   $permissionDescription
	 *
	 * @throws \InvalidArgumentException
	 * @return true|static|string
	 */
	public static function assignRoutesViaPermission($roleName, $permissionName, $routes, $permissionDescription = null)
	{
		$role = static::findOne(['name' => $roleName]);

		if ( !$role )
			throw new \InvalidArgumentException("Role with name = {$roleName} not found");


		$permission = Permission::findOne(['name' => $permissionName]);

		if ( !$permission )
		{
			$permission = Permission::create($permissionName, $permissionDescription);

			if ( $permission->hasErrors() )
				return $permission;
		}

		try
		{
			Yii::$app->db->createCommand()
				->insert('auth_item_child', [
					'parent' => $role->name,
					'child'  => $permission->name,
				])->execute();

		}
		catch (Exception $e)
		{
			// Don't throw Exception because we may have this permission for this role,
			// but need to add new routes to it
		}

		foreach ($routes as $route)
		{
			try
			{
				Yii::$app->db->createCommand()
					->insert('auth_item_child', [
						'parent' => $permission->name,
						'child'  => $route,
					])->execute();
			}
			catch (Exception $e)
			{
				// Don't throw Exception because this permission may already have this route,
				// so just go to the next route
			}
		}



		return true;
	}
}