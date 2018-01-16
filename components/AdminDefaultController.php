<?php
/**
 * Admin Default Controller
 */

namespace wpler\modules\UserManagement\components;

use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Cookie;
use yii\web\NotFoundHttpException;
use Yii;

class AdminDefaultController extends BaseController
{
	/**
	 * @var ActiveRecord
	 */
	public $modelClass;

	/**
	 * @var ActiveRecord
	 */
	public $modelSearchClass;

	/**
	 * @var string
	 */
	public $scenarioOnCreate;

	/**
	 * @var string
	 */
	public $scenarioOnUpdate;

	/**
	 * Actions that will be disabled
	 *
	 * List of available actions:
	 *
	 * ['index', 'view', 'create', 'update', 'delete', 'toggle-attribute',
	 * 'bulk-activate', 'bulk-deactivate', 'bulk-delete', 'grid-sort', 'grid-page-size']
	 *
	 * @var array
	 */
	public $disabledActions = [];

	/**
	 * Opposite to $disabledActions. Every action from AdminDefaultController except those will be disabled
	 *
	 * But if action listed both in $disabledActions and $enableOnlyActions
	 * then it will be disabled
	 *
	 * @var array
	 */
	public $enableOnlyActions = [];

	/**
	 * List of actions in this controller. Needed fo $enableOnlyActions
	 *
	 * @var array
	 */
	protected $_implementedActions = ['index', 'view', 'create', 'update', 'delete', 'toggle-attribute',
		'bulk-activate', 'bulk-deactivate', 'bulk-delete', 'grid-sort', 'grid-page-size'];


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

		return $this->renderIsAjax('index', compact('dataProvider', 'searchModel'));
	}

	/**
	 * Displays a single model.
	 *
	 * @param integer $id
	 *
	 * @return mixed
	 */
	public function actionView($id)
	{
		return $this->renderIsAjax('view', [
			'model' => $this->findModel($id),
		]);
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 * @return mixed
	 */
	public function actionCreate()
	{
		$model = new $this->modelClass;

		if ( $this->scenarioOnCreate )
		{
			$model->scenario = $this->scenarioOnCreate;
		}

		if ( $model->load(Yii::$app->request->post()) && $model->save() )
		{
			$redirect = $this->getRedirectPage('create', $model);

			return $redirect === false ? '' : $this->redirect($redirect);
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

		if ( $this->scenarioOnUpdate )
		{
			$model->scenario = $this->scenarioOnUpdate;
		}

		if ( $model->load(Yii::$app->request->post()) AND $model->save())
		{
			$redirect = $this->getRedirectPage('update', $model);

			return $redirect === false ? '' : $this->redirect($redirect);
		}

		return $this->renderIsAjax('update', compact('model'));
	}

	/**
	 * Deletes an existing model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 *
	 * @param integer $id
	 *
	 * @return mixed
	 */
	public function actionDelete($id)
	{
		$model = $this->findModel($id);
		$model->delete();

		$redirect = $this->getRedirectPage('delete', $model);

		return $redirect === false ? '' : $this->redirect($redirect);
	}
	/**
	 * @param string $attribute
	 * @param int $id
	 */
	public function actionToggleAttribute($attribute, $id)
	{
		$model = $this->findModel($id);
		$model->{$attribute} = ($model->{$attribute} == 1) ? 0 : 1;
		$model->save(false);
	}


	/**
	 * Activate all selected grid items
	 */
	public function actionBulkActivate($attribute = 'active')
	{
		if ( Yii::$app->request->post('selection') )
		{
			$modelClass = $this->modelClass;

			$modelClass::updateAll(
				[$attribute=>1],
				['id'=>Yii::$app->request->post('selection', [])]
			);
		}
	}


	/**
	 * Deactivate all selected grid items
	 */
	public function actionBulkDeactivate($attribute = 'active')
	{
		if ( Yii::$app->request->post('selection') )
		{
			$modelClass = $this->modelClass;

			$modelClass::updateAll(
				[$attribute=>0],
				['id'=>Yii::$app->request->post('selection', [])]
			);
		}
	}

	/**
	 * Deactivate all selected grid items
	 */
	public function actionBulkDelete()
	{
		if ( Yii::$app->request->post('selection') )
		{
			$modelClass = $this->modelClass;

			foreach (Yii::$app->request->post('selection', []) as $id)
			{
				$model = $modelClass::findOne($id);

				if ( $model )
					$model->delete();
			}
		}
	}


	/**
	 * Sorting items in grid
	 */
	public function actionGridSort()
	{
		if ( Yii::$app->request->post('sorter') )
		{
			$sortArray = Yii::$app->request->post('sorter',[]);

			$modelClass = $this->modelClass;

			$models = $modelClass::findAll(array_keys($sortArray));

			foreach ($models as $model)
			{
				$model->sorter = $sortArray[$model->id];
				$model->save(false);
			}

		}
	}


	/**
	 * Set page size for grid
	 */
	public function actionGridPageSize()
	{
		if ( Yii::$app->request->post('grid-page-size') )
		{
			$cookie = new Cookie([
				'name' => '_grid_page_size',
				'value' => Yii::$app->request->post('grid-page-size'),
				'expire' => time() + 86400 * 365, // 1 year
			]);

			Yii::$app->response->cookies->add($cookie);
		}
	}

	/**
	 * Finds the model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @param mixed $id
	 *
	 * @return ActiveRecord the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id)
	{
		$modelClass = $this->modelClass;

		if ( ($model = $modelClass::findOne($id)) !== null )
		{
			return $model;
		}
		else
		{
			throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
		}
	}


	/**
	 * Define redirect page after update, create, delete, etc
	 *
	 * @param string       $action
	 * @param ActiveRecord $model
	 *
	 * @return string|array
	 */
	protected function getRedirectPage($action, $model = null)
	{
		switch ($action)
		{
			case 'delete':
				// Post and ajax request used in grid with pjax. To render normal page we have to redirect request to ['index']
				return Yii::$app->request->isAjax && !Yii::$app->request->isPost ? false : ['index'];
				break;
			case 'update':
				return Yii::$app->request->isAjax ? false : ['view', 'id'=>$model->id];
				break;
			case 'create':
				return Yii::$app->request->isAjax ? false : ['view', 'id'=>$model->id];
				break;
			default:
				return ['index'];
		}
	}

	/**
	 * @inheritdoc
	 */
	public function beforeAction($action)
	{
		if ( parent::beforeAction($action) )
		{
			if ( $this->enableOnlyActions !== [] AND in_array($action->id, $this->_implementedActions) AND !in_array($action->id, $this->enableOnlyActions) )
			{
				throw new NotFoundHttpException('Page not found');
			}

			if ( in_array($action->id, $this->disabledActions) )
			{
				throw new NotFoundHttpException('Page not found');
			}

			return true;
		}

		return false;

	}
}