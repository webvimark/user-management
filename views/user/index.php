<?php

use webvimark\modules\UserManagement\components\GhostHtml;
use webvimark\modules\UserManagement\models\rbacDB\Role;
use webvimark\modules\UserManagement\models\User;
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
 * @var webvimark\modules\UserManagement\models\search\UserSearch $searchModel
 */

$this->title = UserManagementModule::t('back', 'Users');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

	<div class="panel panel-default">
		<div class="panel-heading">
			<strong>
				<span class="glyphicon glyphicon-th"></span>  <?= Html::encode($this->title) ?>
			</strong>

			<?= GridPageSize::widget(['pjaxId'=>'user-grid-pjax']) ?>
		</div>

		<div class="panel-body">

			<div class="row">
				<div class="col-sm-6">
					<p>
						<?= GhostHtml::a(
							'<span class="glyphicon glyphicon-plus-sign"></span> ' . UserManagementModule::t('back', 'Create'),
							['create'],
							['class' => 'btn btn-sm btn-success']
						) ?>
					</p>
				</div>

				<div class="col-sm-6 text-right">
					<?= GridBulkActions::widget(['gridId'=>'user-grid']) ?>
				</div>
			</div>


			<?php Pjax::begin([
				'id'=>'user-grid-pjax',
			]) ?>

			<?= GridView::widget([
				'id'=>'user-grid',
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
						'class'=>'webvimark\components\StatusColumn',
						'attribute'=>'superadmin',
						'toggleUrl'=>Url::to(['toggle-attribute', 'attribute'=>'superadmin', 'id'=>'_id_']),
						'visible'=>Yii::$app->user->isSuperadmin,
					],

					[
						'attribute'=>'username',
						'value'=>function(User $model){
								return Html::a($model->username,['update', 'id'=>$model->id],['data-pjax'=>0]);
							},
						'format'=>'raw',
					],
					[
						'attribute'=>'gridRoleSearch',
						'filter'=>Role::getAvailableRoles(Yii::$app->user->isSuperAdmin, true),
						'value'=>function(User $model){
								return implode(', ', ArrayHelper::map($model->roles, 'name', 'name'));
							},
						'format'=>'raw',
					],
					[
						'value'=>function(User $model){
								return Html::a(
									UserManagementModule::t('back', 'Roles and permissions'),
									['/user-management/user-permission/set', 'id'=>$model->id],
									['class'=>'btn btn-sm btn-primary', 'data-pjax'=>0]);
							},
						'format'=>'raw',
						'visible'=>User::canRoute('/user-management/user-permission/set'),
						'options'=>[
							'width'=>'10px',
						],
					],
					[
						'value'=>function(User $model){
								return Html::a(
									UserManagementModule::t('back', 'Change password'),
									['change-password', 'id'=>$model->id],
									['class'=>'btn btn-sm btn-default', 'data-pjax'=>0]);
							},
						'format'=>'raw',
						'visible'=>User::canRoute('/user-management/user-permission/change-password'),
						'options'=>[
							'width'=>'10px',
						],
					],
					[
						'class'=>'webvimark\components\StatusColumn',
						'attribute'=>'status',
						'toggleUrl'=>User::canRoute('/user-management/user-permission/toggle-attribute') ?
								Url::to(['toggle-attribute', 'attribute'=>'status', 'id'=>'_id_'])
								: false,
						'optionsArray'=>[
							[User::STATUS_ACTIVE, UserManagementModule::t('back', 'Active'), 'success'],
							[User::STATUS_INACTIVE, UserManagementModule::t('back', 'Inactive'), 'danger'],
						],
					],
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
