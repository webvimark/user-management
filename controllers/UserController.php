<?php

namespace leo\modules\UserManagement\controllers;

use leo\components\AdminDefaultController;
use Yii;
use leo\modules\UserManagement\models\User;
use leo\modules\UserManagement\models\search\UserSearch;
use yii\web\NotFoundHttpException;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends AdminDefaultController
{
	/**
	 * @var User
	 */
	public $modelClass = 'leo\modules\UserManagement\models\User';

	/**
	 * @var UserSearch
	 */
	public $modelSearchClass = 'leo\modules\UserManagement\models\search\UserSearch';

	/**
	 * @return mixed|string|\yii\web\Response
	 */
	public function actionCreate()
	{
		$model = new User(['scenario'=>'newUser']);

        if ( $model->load(Yii::$app->request->post())) {
            $model->username = $model->email;

            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
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

        if ( $this->scenarioOnUpdate ) {
            $model->scenario = $this->scenarioOnUpdate;
        }

        if ( $model->load(Yii::$app->request->post())) {
            $model->username = $model->email;

            if ($model->save()) {
                $redirect = $this->getRedirectPage('update', $model);
            }
            return $redirect === false ? '' : $this->redirect($redirect);
        }

        return $this->renderIsAjax('update', compact('model'));
    }

	/**
	 * @param int $id User ID
	 *
	 * @throws \yii\web\NotFoundHttpException
	 * @return string
	 */
	public function actionChangePassword($id)
	{
		$model = User::findOne($id);

		if ( !$model )
		{
			throw new NotFoundHttpException('User not found');
		}

		$model->scenario = 'changePassword';

		if ( $model->load(Yii::$app->request->post()) && $model->save() )
		{
			return $this->redirect(['view',	'id' => $model->id]);
		}

		return $this->renderIsAjax('changePassword', compact('model'));
	}

}
