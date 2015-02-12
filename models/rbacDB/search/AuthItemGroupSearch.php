<?php

namespace webvimark\modules\UserManagement\models\rbacDB\search;

use webvimark\modules\UserManagement\models\rbacDB\AuthItemGroup;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * AuthItemGroupSearch represents the model behind the search form about `app\modules\merchant\models\AuthItemGroup`.
 */
class AuthItemGroupSearch extends AuthItemGroup
{
	public function rules()
	{
		return [
			[['code', 'name', 'created_at', 'updated_at'], 'safe'],
		];
	}

	public function scenarios()
	{
		// bypass scenarios() implementation in the parent class
		return Model::scenarios();
	}

	public function search($params)
	{
		$query = AuthItemGroup::find();

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageSize' => Yii::$app->request->cookies->getValue('_grid_page_size', 20),
			],
			'sort'=>[
				'defaultOrder'=>['created_at'=> SORT_DESC],
			],
		]);

		if (!($this->load($params) && $this->validate())) {
			return $dataProvider;
		}

		if ( $this->created_at )
		{
			$tmp = explode(' - ', $this->created_at);
			if ( isset($tmp[0], $tmp[1]) )
			{
				$query->andFilterWhere(['between',Yii::$app->getModule('user-management')->auth_item_group_table.'.created_at', strtotime($tmp[0]), strtotime($tmp[1])]);
			}
		}

        	$query->andFilterWhere(['like', Yii::$app->getModule('user-management')->auth_item_group_table.'.code', $this->code])
			->andFilterWhere(['like', Yii::$app->getModule('user-management')->auth_item_group_table.'.name', $this->name]);

		return $dataProvider;
	}
}
