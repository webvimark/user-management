<?php

use webvimark\modules\UserManagement\components\GhostHtml;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;
use webvimark\extensions\GridBulkActions\GridBulkActions;
use webvimark\extensions\GridPageSize\GridPageSize;
use yii\grid\GridView;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var webvimark\modules\UserManagement\models\rbacDB\search\AuthItemGroupSearch $searchModel
 */

$this->title = UserManagementModule::t('back', 'Permission groups');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="auth-item-group-index">

	<h2 class="lte-hide-title"><?= $this->title ?></h2>

	<?php // echo $this->render('_search', ['model' => $searchModel]); ?>

	<div class="panel panel-default">

		<div class="panel-body">
			<div class="row">
				<div class="col-sm-6">
					<p>
						<?= GhostHtml::a(
							'<span class="glyphicon glyphicon-plus-sign"></span> ' . UserManagementModule::t('back', 'Create'),
							['create'],
							['class' => 'btn btn-success']
						) ?>
					</p>
				</div>

				<div class="col-sm-6 text-right">
					<?= GridPageSize::widget(['pjaxId'=>'auth-item-group-grid-pjax']) ?>
				</div>
			</div>


			<?php Pjax::begin([
				'id'=>'auth-item-group-grid-pjax',
			]) ?>

			<?= GridView::widget([
				'id'=>'auth-item-group-grid',
				'dataProvider' => $dataProvider,
				'pager'=>[
					'options'=>['class'=>'pagination pagination-sm'],
					'hideOnSinglePage'=>true,
					'lastPageLabel'=>'>>',
					'firstPageLabel'=>'<<',
				],
				'layout'=>'{items}<div class="row"><div class="col-sm-8">{pager}</div><div class="col-sm-4 text-right">{summary}'.GridBulkActions::widget([
						'gridId'=>'auth-item-group-grid',
						'actions'=>[Url::to(['bulk-delete'])=>GridBulkActions::t('app', 'Delete'),],
						]).'</div></div>',
				'filterModel' => $searchModel,
				'columns' => [
					['class' => 'yii\grid\SerialColumn', 'options'=>['style'=>'width:10px'] ],

					[
						'attribute'=>'name',
						'value'=>function($model){
								return Html::a($model->name, ['update', 'id'=>$model->code], ['data-pjax'=>0]);
							},
						'format'=>'raw',
					],
					'code',

					['class' => 'yii\grid\CheckboxColumn', 'options'=>['style'=>'width:10px'] ],
					[
						'class' => 'yii\grid\ActionColumn',
						'contentOptions'=>['style'=>'width:70px; text-align:center;'],
					],
				],
			]); ?>
		
			<?php Pjax::end() ?>
		</div>
	</div>
</div>
