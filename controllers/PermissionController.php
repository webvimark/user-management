<?php

namespace webvimark\modules\UserManagement\controllers;


use webvimark\modules\UserManagement\components\AuthHelper;
use webvimark\modules\UserManagement\models\rbacDB\Permission;
use webvimark\modules\UserManagement\models\rbacDB\Route;
use webvimark\modules\UserManagement\models\rbacDB\search\PermissionSearch;
use webvimark\components\AdminDefaultController;
use yii\db\Query;
use Yii;
use yii\rbac\DbManager;

class PermissionController extends AdminDefaultController
{
	/**
	 * @var Permission
	 */
	public $modelClass = 'webvimark\modules\UserManagement\models\rbacDB\Permission';

	/**
	 * @var PermissionSearch
	 */
	public $modelSearchClass = 'webvimark\modules\UserManagement\models\rbacDB\search\PermissionSearch';

	/**
	 * @param string $id
	 *
	 * @return string
	 */
	public function actionView($id)
	{
		$item = $this->findModel($id);

		$routes = Route::find()->asArray()->all();

		$permissions = Permission::find()
			->andWhere(['not in', 'auth_item.name', [Yii::$app->getModule('user-management')->commonPermissionName, $id]])
			->joinWith('group')
			->all();

		$permissionsByGroup = [];
		foreach ($permissions as $permission)
		{
			$permissionsByGroup[@$permission->group->name][] = $permission;
		}

		$authManager = new DbManager();

		$currentRoutesAndPermissions = AuthHelper::separateRoutesAndPermissions($authManager->getChildren($item->name));

		$childRoutes = $currentRoutesAndPermissions->routes;
		$childPermissions = $currentRoutesAndPermissions->permissions;

		return $this->render('view', compact('item', 'childPermissions', 'routes', 'permissionsByGroup', 'childRoutes'));
	}

	/**
	 * Add or remove child permissions (including routes) and return back to view
	 *
	 * @param string $id
	 */
	public function actionSetChildPermissions($id)
	{
		$item = $this->findModel($id);

		$authManager = new DbManager();

		$newChildPermissions = Yii::$app->request->post('child_permissions', []);

		$oldChildPermissions = array_keys($authManager->getChildren($item->name));

		$toRemove = array_diff($oldChildPermissions, $newChildPermissions);
		$toAdd = array_diff($newChildPermissions, $oldChildPermissions);

		Permission::addChildren($item->name, $toAdd);
		Permission::removeChildren($item->name, $toRemove);

		$this->redirect(['view', 'id'=>$id]);
	}

	/**
	 * Add or remove routes for this permission
	 *
	 * @param string $id
	 */
	public function actionSetChildRoutes($id)
	{
		$item = $this->findModel($id);

		$newRoutes = Yii::$app->request->post('child_routes', []);

		$oldRoutes = (new Query())
			->select(['child'])
			->from('auth_item_child')
			->where(['parent'=>$id])
			->column();

		$toAdd = array_diff($newRoutes, $oldRoutes);
		$toRemove = array_diff($oldRoutes, $newRoutes);

		foreach ($toAdd as $addItem)
		{
			Yii::$app->db->createCommand()
				->insert('auth_item_child', [
					'parent'=>$id,
					'child'=>$addItem,
				])->execute();
		}

		foreach ($toRemove as $removeItem)
		{
			Yii::$app->db->createCommand()
				->delete('auth_item_child', [
					'parent'=>$id,
					'child'=>$removeItem,
				])->execute();
		}


		if ( ( $toAdd OR $toRemove ) AND ( $id == Yii::$app->getModule('user-management')->commonPermissionName ) )
		{
			Yii::$app->cache->delete('__commonRoutes');
		}

		AuthHelper::invalidatePermissions();

		$this->redirect(['view', 'id'=>$id]);
	}

	/**
	 * Add new routes and remove unused (for example if module or controller was deleted)
	 *
	 * @param string $id
	 */
	public function actionRefreshRoutes($id)
	{
		Route::refreshRoutes();

		$this->redirect(['view', 'id'=>$id]);
	}


	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 * @return mixed
	 */
	public function actionCreate()
	{
		$model = new Permission();
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