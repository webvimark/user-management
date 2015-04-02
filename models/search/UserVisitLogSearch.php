<?php

namespace webvimark\modules\UserManagement\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use webvimark\modules\UserManagement\models\UserVisitLog;

/**
 * UserVisitLogSearch represents the model behind the search form about `webvimark\modules\UserManagement\models\UserVisitLog`.
 */
class UserVisitLogSearch extends UserVisitLog
{
	public function rules()
	{
		return [
			[['id'], 'integer'],
			[['token', 'ip', 'language', 'user_id', 'os', 'browser', 'visit_time'], 'safe'],
		];
	}

	public function scenarios()
	{
		// bypass scenarios() implementation in the parent class
		return Model::scenarios();
	}

	public function search($params)
	{
		$query = UserVisitLog::find();

		$query->joinWith(['user']);

		// Don't let non-superadmin view superadmin activity
		if ( !Yii::$app->user->isSuperadmin )
		{
			$query->andWhere([Yii::$app->getModule('user-management')->user_table . '.superadmin'=>0]);
		}

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageSize' => Yii::$app->request->cookies->getValue('_grid_page_size', 20),
			],
			'sort'=>[
				'defaultOrder'=>['id'=> SORT_DESC],
			],
		]);

		if (!($this->load($params) && $this->validate())) {
			return $dataProvider;
		}

		if ( $this->visit_time )
		{
			$tmp = explode(' - ', $this->visit_time);
			if ( isset($tmp[0], $tmp[1]) )
			{
				$query->andFilterWhere(['between', $this->tableName() . '.visit_time', strtotime($tmp[0]), strtotime($tmp[1])]);
			}
		}

		$query->andFilterWhere([
			$this->tableName() . '.id' => $this->id,
		]);

        	$query->andFilterWhere(['like', Yii::$app->getModule('user-management')->user_table . '.username', $this->user_id])
			->andFilterWhere(['like', $this->tableName() . '.ip', $this->ip])
			->andFilterWhere(['like', $this->tableName() . '.os', $this->os])
			->andFilterWhere(['like', $this->tableName() . '.browser', $this->browser])
			->andFilterWhere(['like', $this->tableName() . '.language', $this->language]);

		return $dataProvider;
	}
}
