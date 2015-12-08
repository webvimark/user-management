<?php
namespace webvimark\modules\UserManagement\models\rbacDB;

use Exception;
use webvimark\modules\UserManagement\components\AuthHelper;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rbac\DbManager;

class Role extends AbstractItem
{
	const ITEM_TYPE = self::TYPE_ROLE;

	/**
	 * @param int     $userId
	 *
	 * @return array|\yii\rbac\Role[]
	 */
	public static function getUserRoles($userId)
	{
		$dbManager = Yii::$app->authManager instanceof DbManager ? Yii::$app->authManager : new DbManager();

		return $dbManager->getRolesByUser($userId);
	}

	/**
	 * Get permissions assigned to this role or its children
	 *
	 * @param string $roleName
	 * @param bool   $asArray
	 *
	 * @return array|Permission[]
	 */
	public static function getPermissionsByRole($roleName, $asArray = true)
	{
		$dbManager = Yii::$app->authManager instanceof DbManager ? Yii::$app->authManager : new DbManager();

		$rbacPermissions = $dbManager->getPermissionsByRole($roleName);

		$permissionNames = ArrayHelper::map($rbacPermissions, 'name', 'description');

		return $asArray ? $permissionNames : Permission::find()->andWhere(['name'=>array_keys($permissionNames)])->all();
	}

	/**
	 * Return only roles, that are assigned to the current user.
	 * Return all if superadmin
	 * Useful for forms where user can give roles to another users, but we restrict him only with roles he possess
	 *
	 * @param bool $showAll
	 * @param bool $asArray
	 *
	 * @return static[]
	 */
	public static function getAvailableRoles($showAll = false, $asArray = false)
	{
		$condition = (Yii::$app->user->isSuperAdmin OR $showAll) ? [] : ['name'=>Yii::$app->session->get(AuthHelper::SESSION_PREFIX_ROLES)];

		$result = static::find()->andWhere($condition)->all();

		return $asArray ? ArrayHelper::map($result, 'name', 'name') : $result;
	}

	/**
	 * Assign route to role via permission and create permission or route if it don't exists
	 * Helper mainly for migrations
	 *
	 * @param string      $roleName
	 * @param string      $permissionName
	 * @param array       $routes
	 * @param null|string $permissionDescription
	 * @param null|string $groupCode
	 *
	 * @throws \InvalidArgumentException
	 * @return true|static|string
	 */
	public static function assignRoutesViaPermission($roleName, $permissionName, $routes, $permissionDescription = null, $groupCode = null)
	{
		$role = static::findOne(['name' => $roleName]);

		if ( !$role )
			throw new \InvalidArgumentException("Role with name = {$roleName} not found");


		$permission = Permission::findOne(['name' => $permissionName]);

		if ( !$permission )
		{
			$permission = Permission::create($permissionName, $permissionDescription, $groupCode);

			if ( $permission->hasErrors() )
				return $permission;
		}

		try
		{
			Yii::$app->db->createCommand()
				->insert(Yii::$app->getModule('user-management')->auth_item_child_table, [
					'parent' => $role->name,
					'child'  => $permission->name,
				])->execute();

		}
		catch (Exception $e)
		{
			// Don't throw Exception because we may have this permission for this role,
			// but need to add new routes to it
		}

		$routes = (array)$routes;

		foreach ($routes as $route)
		{
			$route = '/'. ltrim($route, '/');

			Route::create($route);

			try
			{
				Yii::$app->db->createCommand()
					->insert(Yii::$app->getModule('user-management')->auth_item_child_table, [
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

		AuthHelper::invalidatePermissions();

		return true;
	}
}