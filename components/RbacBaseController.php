<?php

namespace webvimark\modules\UserManagement\components;


use webvimark\modules\UserManagement\forms\ItemForm;
use yii\data\ActiveDataProvider;
use yii\rbac\Role;
use yii\rbac\Permission;
use yii\web\NotFoundHttpException;
use Yii;

class RbacBaseController extends AccessController
{
	/**
	 * @var int - ItemForm::TYPE_ROLE or ItemForm::TYPE_PERMISSION
	 */
	public $itemType;


	/**
	 * Lists all models.
	 * @return mixed
	 */
	public function actionIndex()
	{
		$searchModel  = $this->modelSearchClass ? new $this->modelSearchClass : null;

		if ( $searchModel )
		{
			$dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());
		}
		else
		{
			$modelClass = $this->modelClass;
			$dataProvider = new ActiveDataProvider([
				'query' => $modelClass::find(),
			]);
		}

		return $this->render('index', compact('dataProvider', 'searchModel'));
	}

	/**
	 * @return string
	 */
	public function actionCreate()
	{
		$model = new ItemForm(['type'=>$this->itemType]);

		if ( $model->load(\Yii::$app->request->post()) AND $model->validate() AND $model->configureItem('add') )
		{
			$this->redirect(['index']);
		}

		return $this->render('create', compact('model'));
	}

	/**
	 * @param string $id
	 *
	 * @throws \yii\web\NotFoundHttpException
	 * @return string
	 */
	public function actionUpdate($id)
	{
		$model = new ItemForm(['type'=>$this->itemType]);

		$item = $this->loadItem($id);

		$model->fillFields($item, $model);
		$model->oldName = $item->name;

		if ( $model->load(\Yii::$app->request->post()) AND $model->validate() AND $model->configureItem('update') )
		{
			$this->redirect(['index']);
		}

		return $this->render('update', compact('model'));
	}

	/**
	 * @param string $id
	 *
	 * @return string
	 */
	public function actionView($id)
	{
		$role = $this->loadItem($id);

		$permissions = \Yii::$app->authManager->getPermissionsByRole($role->name);

		$roles = \Yii::$app->authManager->getRoles();

		unset($roles[$role->name]);

		return $this->render('view', compact('role', 'roles', 'permissions'));
	}

	/**
	 * @param string $id
	 *
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionDelete($id)
	{
		$item = $this->loadItem($id);

		\Yii::$app->authManager->remove($item);

		$this->redirect(['index']);

	}

	/**
	 * @param string $id
	 *
	 * @return Role|Permission
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function loadItem($id)
	{
		if ( $this->itemType == ItemForm::TYPE_ROLE )
		{
			$item = \Yii::$app->authManager->getRole($id);
		}
		else
		{
			$item = \Yii::$app->authManager->getPermission($id);
		}

		if ( !$item )
		{
			throw new NotFoundHttpException(\Yii::t('auth', "Item not found"));
		}

		return $item;
	}

	/**
	 * @inheritdoc
	 */
	public function beforeAction($action)
	{
		$this->layout = $this->module->rbacLayout;

		return parent::beforeAction($action);
	}
} 