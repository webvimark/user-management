<?php
use webvimark\modules\UserManagement\UserManagementModule;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/**
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var webvimark\modules\UserManagement\models\rbacDB\search\PermissionSearch $searchModel
 * @var yii\web\View $this
 */
$this->title = UserManagementModule::t('back', 'Permissions');
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="panel panel-default">
	<div class="panel-heading">
		<strong>
			<span class="glyphicon glyphicon-th"></span> <?= $this->title ?>
		</strong>
	</div>
	<div class="panel-body">
		<p>
			<?= Html::a(
				'<span class="glyphicon glyphicon-plus-sign"></span> ' . UserManagementModule::t('back', 'Create'),
				['create'],
				['class' => 'btn btn-sm btn-success']
			) ?>
		</p>

		<?php Pjax::begin([
			'id'=>'role-grid-pjax',
		]) ?>

		<?= GridView::widget([
			'id'=>'role-grid',
			'dataProvider' => $dataProvider,
			'pager'=>[
				'options'=>['class'=>'pagination pagination-sm'],
				'hideOnSinglePage'=>true,
				'lastPageLabel'=>'>>',
				'firstPageLabel'=>'<<',
			],
			'filterModel' => $searchModel,
			'layout'=>'{items}<div class="row"><div class="col-sm-8">{pager}</div><div class="col-sm-4 text-right">{summary}</div></div>',
			'columns' => [
				['class' => 'yii\grid\SerialColumn', 'options'=>['style'=>'width:10px'] ],

				[
					'attribute'=>'name',
					'value'=>function($model){
							if ( $model->name == Yii::$app->getModule('user-management')->commonPermissionName )
							{
								return Html::a(
									$model->name,
									['view', 'id'=>$model->name],
									['data-pjax'=>0, 'class'=>'label label-primary']
								);
							}
							else
							{
								return Html::a($model->name, ['view', 'id'=>$model->name], ['data-pjax'=>0]);
							}
						},
					'format'=>'raw',
				],
				'description',

				[
					'class' => 'yii\grid\ActionColumn',
					'contentOptions'=>['style'=>'width:70px; text-align:center;'],
				],
			],
		]); ?>

		<?php Pjax::end() ?>
	</div>
</div>