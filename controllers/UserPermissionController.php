<?php

namespace app\webvimark\modules\UserManagement\controllers;

use app\webvimark\modules\UserManagement\components\RbacBaseController;
use app\webvimark\modules\UserManagement\models\User;
use yii\web\NotFoundHttpException;

class UserPermissionController extends RbacBaseController
{
	public $enableBaseActions = false;

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

		return $this->render('set', compact('user'));
	}

	/**
	 * @param int $id - User ID
	 */
	public function actionSetRoles($id)
	{
		$oldAssignments = array_keys(\Yii::$app->authManager->getRolesByUser($id));
		$newAssignments = \Yii::$app->request->post('roles', []);

		$toAssign = array_diff($newAssignments, $oldAssignments);
		$toRevoke = array_diff($oldAssignments, $newAssignments);

		foreach ($toRevoke as $item)
		{
			$role = \Yii::$app->authManager->getRole($item);
			\Yii::$app->authManager->revoke($role, $id);
		}

		foreach ($toAssign as $item)
		{
			$role = \Yii::$app->authManager->getRole($item);

			\Yii::$app->authManager->assign($role, $id);
		}

		$this->redirect(['set', 'id'=>$id]);
	}

	/**
	 * @param int $id - User ID
	 */
	public function actionSetPermissions($id)
	{
		$oldPermissions = array_keys(\Yii::$app->authManager->getPermissionsByUser($id));
		$newPermissions = \Yii::$app->request->post('permissions', []);

		$toAdd = array_diff($newPermissions, $oldPermissions);
		$toRemove = array_diff($oldPermissions, $newPermissions);

		foreach ($toRemove as $item)
		{
			$permission = \Yii::$app->authManager->getPermission($item);
			\Yii::$app->authManager->revoke($permission, $id);
		}

		foreach ($toAdd as $item)
		{
			$permission = \Yii::$app->authManager->getPermission($item);

			\Yii::$app->authManager->assign($permission, $id);
		}

		$this->redirect(['view', 'id'=>$id]);
	}
}
