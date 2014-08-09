<?php
namespace webvimark\modules\UserManagement\models\rbacDB;

use Exception;
use Yii;
use yii\rbac\DbManager;

class Permission extends AbstractItem
{
	const ITEM_TYPE = self::TYPE_PERMISSION;

	/**
	 * @param int $userId
	 *
	 * @return array|\yii\rbac\Permission[]
	 */
	public static function getUserPermissions($userId)
	{
		return (new DbManager())->getPermissionsByUser($userId);
	}

	/**
	 * Assign route to permission and create them if they don't exists
	 * Helper mainly for migrations
	 *
	 * @param string $permissionName
	 * @param array $routes
	 * @param null   $permissionDescription
	 *
	 * @throws \InvalidArgumentException
	 * @return true|static|string
	 */
	public static function assignRoutes($permissionName, $routes, $permissionDescription = null)
	{
		$permission = static::findOne(['name' => $permissionName]);

		if ( !$permission )
		{
			$permission = static::create($permissionName, $permissionDescription);

			if ( $permission->hasErrors() )
			{
				return $permission;
			}
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