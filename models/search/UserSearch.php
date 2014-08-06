<?php

namespace app\webvimark\modules\UserManagement\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\webvimark\modules\UserManagement\models\User;

/**
 * UserSearch represents the model behind the search form about `app\webvimark\modules\UserManagement\models\User`.
 */
class UserSearch extends User
{
	public function rules()
	{
		return [
			[['id', 'superadmin', 'status', 'created_at', 'updated_at'], 'integer'],
			[['username', 'gridRoleSearch'], 'string'],
		];
	}

	public function scenarios()
	{
		// bypass scenarios() implementation in the parent class
		return Model::scenarios();
	}

	public function search($params)
	{
		$query = User::find();

//		$query->joinWith(['roles']);
		$query->with(['roles']);

		if ( !Yii::$app->user->isSuperadmin )
		{
			$query->where(['superadmin'=>0]);
		}

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageSize' => \Yii::$app->request->cookies->getValue('_grid_page_size', 20),
			],
			'sort'=>[
				'defaultOrder'=>[
					'id'=>SORT_DESC,
				],
			],
		]);

		if (!($this->load($params) && $this->validate())) {
			return $dataProvider;
		}

		$query->andFilterWhere([
			'id' => $this->id,
			'superadmin' => $this->superadmin,
			'status' => $this->status,
			'auth_item.name' => $this->gridRoleSearch,
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at,
		]);

        	$query->andFilterWhere(['like', 'username', $this->username]);

		return $dataProvider;
	}
}
