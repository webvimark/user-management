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
			[['id', 'visit_time'], 'integer'],
			[['token', 'ip', 'language', 'user_id', 'browser_and_os'], 'safe'],
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

		$query->andFilterWhere([
			'user_visit_log.id' => $this->id,
			'user_visit_log.visit_time' => $this->visit_time,
		]);

        	$query->andFilterWhere(['like', 'user.username', $this->user_id])
			->andFilterWhere(['like', 'user_visit_log.ip', $this->ip])
			->andFilterWhere(['like', 'user_visit_log.language', $this->language]);

		return $dataProvider;
	}
}
