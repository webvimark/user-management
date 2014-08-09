<?php

namespace webvimark\modules\UserManagement\controllers;

use webvimark\modules\UserManagement\components\RbacBaseController;
use webvimark\modules\UserManagement\models\User;
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
}
