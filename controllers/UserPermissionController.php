<?php

namespace webvimark\modules\UserManagement\controllers;

use webvimark\components\BaseController;
use webvimark\modules\UserManagement\models\rbacDB\Permission;
use webvimark\modules\UserManagement\models\rbacDB\Role;
use webvimark\modules\UserManagement\models\User;
use yii\web\NotFoundHttpException;
use Yii;

class UserPermissionController extends BaseController
{

	/**
	 * @param int $id User ID
	 *
	 * @throws \yii\web\NotFoundHttpException
	 * @return string
	 */
	public function actionSet($id)
	{
		$user = User::findOne($id);

		if ( !$user )
		{
			throw new NotFoundHttpException('User not found');
		}

		$permissionsByGroup = [];
		$permissions = Permission::find()
			->andWhere([
				'auth_item.name'=>array_keys(Permission::getUserPermissions($user->id))
			])
			->joinWith('group')
			->all();

		foreach ($permissions as $permission)
		{
			$permissionsByGroup[@$permission->group->name][] = $permission;
		}

		return $this->renderIsAjax('set', compact('user', 'permissionsByGroup'));
	}

	/**
	 * @param int $id - User ID
	 */
	public function actionSetRoles($id)
	{
		$oldAssignments = array_keys(Role::getUserRoles($id));

		// To be sure that user didn't attempt to assign himself some unavailable roles
		$newAssignments = array_intersect(Role::getAvailableRoles(Yii::$app->user->isSuperAdmin, true), Yii::$app->request->post('roles', []));

		$toAssign = array_diff($newAssignments, $oldAssignments);
		$toRevoke = array_diff($oldAssignments, $newAssignments);

		foreach ($toRevoke as $role)
		{
			User::revokeRole($id, $role);
		}

		foreach ($toAssign as $role)
		{
			User::assignRole($id, $role);
		}

		$this->redirect(['set', 'id'=>$id]);
	}
}
