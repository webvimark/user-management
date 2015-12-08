<?php

namespace webvimark\modules\UserManagement\controllers;

use webvimark\modules\UserManagement\components\AuthHelper;
use webvimark\modules\UserManagement\models\rbacDB\Permission;
use webvimark\modules\UserManagement\models\rbacDB\Role;
use webvimark\modules\UserManagement\models\rbacDB\search\RoleSearch;
use webvimark\components\AdminDefaultController;
use webvimark\modules\UserManagement\UserManagementModule;
use Yii;
use yii\rbac\DbManager;

class RoleController extends AdminDefaultController
{
	/**
	 * @var Role
	 */
	public $modelClass = 'webvimark\modules\UserManagement\models\rbacDB\Role';

	/**
	 * @var RoleSearch
	 */
	public $modelSearchClass = 'webvimark\modules\UserManagement\models\rbacDB\search\RoleSearch';

	/**
	 * @param string $id
	 *
	 * @return string
	 */
	public function actionView($id)
	{
		$role = $this->findModel($id);

		$authManager = Yii::$app->authManager instanceof DbManager ? Yii::$app->authManager : new DbManager();

		$allRoles = Role::find()
			->asArray()
			->andWhere('name != :current_name', [':current_name'=>$id])
			->all();

		$permissions = Permission::find()
			->andWhere(Yii::$app->getModule('user-management')->auth_item_table . '.name != :commonPermissionName', [':commonPermissionName'=>Yii::$app->getModule('user-management')->commonPermissionName])
			->joinWith('group')
			->all();

		$permissionsByGroup = [];
		foreach ($permissions as $permission)
		{
			$permissionsByGroup[@$permission->group->name][] = $permission;
		}

		$childRoles = $authManager->getChildren($role->name);

		$currentRoutesAndPermissions = AuthHelper::separateRoutesAndPermissions($authManager->getPermissionsByRole($role->name));

		$currentPermissions = $currentRoutesAndPermissions->permissions;

		return $this->renderIsAjax('view', compact('role', 'allRoles', 'childRoles', 'currentPermissions', 'permissionsByGroup'));
	}

	/**
	 * Add or remove child roles and return back to view
	 *
	 * @param string $id
	 *
	 * @return \yii\web\Response
	 */
	public function actionSetChildRoles($id)
	{
		$role = $this->findModel($id);

		$newChildRoles = Yii::$app->request->post('child_roles', []);

		$dbManager = Yii::$app->authManager instanceof DbManager ? Yii::$app->authManager : new DbManager();

		$children = $dbManager->getChildren($role->name);

		$oldChildRoles = [];

		foreach ($children as $child)
		{
			if ( $child->type == Role::TYPE_ROLE )
			{
				$oldChildRoles[$child->name] = $child->name;
			}
		}

		$toRemove = array_diff($oldChildRoles, $newChildRoles);
		$toAdd = array_diff($newChildRoles, $oldChildRoles);

		Role::addChildren($role->name, $toAdd);
		Role::removeChildren($role->name, $toRemove);

		Yii::$app->session->setFlash('success', UserManagementModule::t('back', 'Saved'));

		return $this->redirect(['view', 'id'=>$id]);
	}

	/**
	 * Add or remove child permissions (including routes) and return back to view
	 *
	 * @param string $id
	 *
	 * @return \yii\web\Response
	 */
	public function actionSetChildPermissions($id)
	{
		$role = $this->findModel($id);

		$newChildPermissions = Yii::$app->request->post('child_permissions', []);

		$dbManager = Yii::$app->authManager instanceof DbManager ? Yii::$app->authManager : new DbManager();

		$oldChildPermissions = array_keys($dbManager->getPermissionsByRole($role->name));

		$toRemove = array_diff($oldChildPermissions, $newChildPermissions);
		$toAdd = array_diff($newChildPermissions, $oldChildPermissions);

		Role::addChildren($role->name, $toAdd);
		Role::removeChildren($role->name, $toRemove);

		Yii::$app->session->setFlash('success', UserManagementModule::t('back', 'Saved'));

		return $this->redirect(['view', 'id'=>$id]);
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 * @return mixed
	 */
	public function actionCreate()
	{
		$model = new Role;
		$model->scenario = 'webInput';

		if ( $model->load(Yii::$app->request->post()) && $model->save() )
		{
			return $this->redirect(['view', 'id'=>$model->name]);
		}

		return $this->renderIsAjax('create', compact('model'));
	}

	/**
	 * Updates an existing model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 *
	 * @param integer $id
	 *
	 * @return mixed
	 */
	public function actionUpdate($id)
	{
		$model = $this->findModel($id);
		$model->scenario = 'webInput';

		if ( $model->load(Yii::$app->request->post()) AND $model->save())
		{
			return $this->redirect(['view', 'id'=>$model->name]);
		}

		return $this->renderIsAjax('update', compact('model'));
	}
}