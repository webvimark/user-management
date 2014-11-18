<?php

namespace webvimark\modules\UserManagement\controllers;

use webvimark\modules\UserManagement\components\AuthHelper;
use webvimark\modules\UserManagement\models\rbacDB\Permission;
use webvimark\modules\UserManagement\models\rbacDB\Role;
use webvimark\modules\UserManagement\models\rbacDB\search\RoleSearch;
use webvimark\components\AdminDefaultController;
use yii\filters\VerbFilter;
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
	 * Set layout from config
	 *
	 * @inheritdoc
	 */
	public function beforeAction($action)
	{
		if ( parent::beforeAction($action) )
		{
			$layouts = $this->module->layouts[$this->id];

			if ( isset($layouts[$action->id]) )
			{
				$this->layout = $layouts[$action->id];
			}
			elseif ( isset($layouts['*']) )
			{
				$this->layout = $layouts['*'];
			}

			return true;
		}

		return false;
	}

	/**
	 * @param string $id
	 *
	 * @return string
	 */
	public function actionView($id)
	{
		$role = $this->findModel($id);

		$authManager = new DbManager();

		$allRoles = Role::find()
			->asArray()
			->andWhere('name != :current_name', [':current_name'=>$id])
			->all();

		$permissions = Permission::find()
			->andWhere('name != :commonPermissionName', [':commonPermissionName'=>Yii::$app->getModule('user-management')->commonPermissionName])
			->asArray()
			->all();

		$childRoles = $authManager->getChildren($role->name);

		$currentRoutesAndPermissions = AuthHelper::separateRoutesAndPermissions($authManager->getPermissionsByRole($role->name));

		$currentPermissions = $currentRoutesAndPermissions->permissions;

		return $this->render('view', compact('role', 'allRoles', 'childRoles', 'permissions', 'currentPermissions'));
	}

	/**
	 * Add or remove child roles and return back to view
	 *
	 * @param string $id
	 */
	public function actionSetChildRoles($id)
	{
		$role = $this->findModel($id);

		$authManager = new DbManager();

		$newChildRoles = Yii::$app->request->post('child_roles', []);

		$oldChildRoles = array_keys($authManager->getChildren($role->name));

		$toRemove = array_diff($oldChildRoles, $newChildRoles);
		$toAdd = array_diff($newChildRoles, $oldChildRoles);

		foreach ($toAdd as $addItem)
		{
			$authManager->addChild($role, $authManager->getRole($addItem));
		}

		foreach ($toRemove as $removeItem)
		{
			$authManager->removeChild($role, $authManager->getRole($removeItem));
		}

		AuthHelper::invalidatePermissions();

		$this->redirect(['view', 'id'=>$id]);
	}

	/**
	 * Add or remove child permissions (including routes) and return back to view
	 *
	 * @param string $id
	 */
	public function actionSetChildPermissions($id)
	{
		$role = $this->findModel($id);

		$authManager = new DbManager();

		$newChildPermissions = Yii::$app->request->post('child_permissions', []);

		$oldChildPermissions = array_keys($authManager->getPermissionsByRole($role->name));

		$toRemove = array_diff($oldChildPermissions, $newChildPermissions);
		$toAdd = array_diff($newChildPermissions, $oldChildPermissions);

		foreach ($toAdd as $addItem)
		{
			$authManager->addChild($role, $authManager->getPermission($addItem));
		}

		foreach ($toRemove as $removeItem)
		{
			$authManager->removeChild($role, $authManager->getPermission($removeItem));
		}

		AuthHelper::invalidatePermissions();

		$this->redirect(['view', 'id'=>$id]);
	}

	/**
	 * @inheritdoc
	 */
	protected function getRedirectPage($action, $model = null)
	{
		switch ($action)
		{
			case 'delete':
				return ['index'];
				break;
			case 'update':
				return ['view', 'id'=>$model->name];
				break;
			case 'create':
				return ['view', 'id'=>$model->name];
				break;
			default:
				return ['index'];
		}
	}
}