<?php

use webvimark\modules\UserManagement\components\GhostHtml;
use webvimark\modules\UserManagement\models\rbacDB\Role;
use webvimark\modules\UserManagement\models\User;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
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

	<h2 class="lte-hide-title"><?= $this->title ?></h2>

	<div class="panel panel-default">
		<div class="panel-body">

			<div class="row">
				<div class="col-sm-6">
					<p>
						<?= GhostHtml::a(
							'<span class="glyphicon glyphicon-plus-sign"></span> ' . UserManagementModule::t('back', 'Create'),
							['/user-management/user/create'],
							['class' => 'btn btn-success']
						) ?>
					</p>
				</div>

				<div class="col-sm-6 text-right">
					<?= GridPageSize::widget(['pjaxId'=>'user-grid-pjax']) ?>
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
				'layout'=>'{items}<div class="row"><div class="col-sm-8">{pager}</div><div class="col-sm-4 text-right">{summary}'.GridBulkActions::widget([
						'gridId'=>'user-grid',
						'actions'=>[
							Url::to(['bulk-activate', 'attribute'=>'status'])=>GridBulkActions::t('app', 'Activate'),
							Url::to(['bulk-deactivate', 'attribute'=>'status'])=>GridBulkActions::t('app', 'Deactivate'),
							'----'=>[
								Url::to(['bulk-delete'])=>GridBulkActions::t('app', 'Delete'),
							],
						],
					]).'</div></div>',
				'columns' => [
					['class' => 'yii\grid\SerialColumn', 'options'=>['style'=>'width:10px'] ],

					[
						'class'=>'webvimark\components\StatusColumn',
						'attribute'=>'superadmin',
						'visible'=>Yii::$app->user->isSuperadmin,
					],

					[
						'attribute'=>'username',
						'value'=>function(User $model){
								return Html::a($model->username,['view', 'id'=>$model->id],['data-pjax'=>0]);
							},
						'format'=>'raw',
					],
					[
						'attribute'=>'email',
						'format'=>'raw',
						'visible'=>User::hasPermission('viewUserEmail'),
					],
					[
						'class'=>'webvimark\components\StatusColumn',
						'attribute'=>'email_confirmed',
						'visible'=>User::hasPermission('viewUserEmail'),
					],
					[
						'attribute'=>'gridRoleSearch',
						'filter'=>ArrayHelper::map(Role::getAvailableRoles(Yii::$app->user->isSuperAdmin),'name', 'description'),
						'value'=>function(User $model){
								return implode(', ', ArrayHelper::map($model->roles, 'name', 'description'));
							},
						'format'=>'raw',
						'visible'=>User::hasPermission('viewUserRoles'),
					],
					[
						'attribute'=>'registration_ip',
						'value'=>function(User $model){
								return Html::a($model->registration_ip, "http://ipinfo.io/" . $model->registration_ip, ["target"=>"_blank"]);
							},
						'format'=>'raw',
						'visible'=>User::hasPermission('viewRegistrationIp'),
					],
					[
						'value'=>function(User $model){
								return GhostHtml::a(
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
								return GhostHtml::a(
									UserManagementModule::t('back', 'Change password'),
									['change-password', 'id'=>$model->id],
									['class'=>'btn btn-sm btn-default', 'data-pjax'=>0]);
							},
						'format'=>'raw',
						'options'=>[
							'width'=>'10px',
						],
					],
					[
						'class'=>'webvimark\components\StatusColumn',
						'attribute'=>'status',
						'optionsArray'=>[
							[User::STATUS_ACTIVE, UserManagementModule::t('back', 'Active'), 'success'],
							[User::STATUS_INACTIVE, UserManagementModule::t('back', 'Inactive'), 'warning'],
							[User::STATUS_BANNED, UserManagementModule::t('back', 'Banned'), 'danger'],
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
