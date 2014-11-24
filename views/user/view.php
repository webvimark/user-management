<?php

use webvimark\modules\UserManagement\models\User;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var webvimark\modules\UserManagement\models\User $model
 */

$this->title = $model->username;
$this->params['breadcrumbs'][] = ['label' => UserManagementModule::t('back', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-view">
	<div class="panel panel-default">
		<div class="panel-body">

		    <p>
			<?= Html::a(UserManagementModule::t('back', 'Edit'), ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary']) ?>
			<?= Html::a(UserManagementModule::t('back', 'Create'), ['create'], ['class' => 'btn btn-sm btn-success']) ?>
			<?= Html::a(
				UserManagementModule::t('back', 'Roles and permissions'),
				['/user-management/user-permission/set', 'id'=>$model->id],
				['class' => 'btn btn-sm btn-default']
			) ?>

			<?= Html::a(UserManagementModule::t('back', 'Delete'), ['delete', 'id' => $model->id], [
			    'class' => 'btn btn-sm btn-danger pull-right',
			    'data' => [
				'confirm' => UserManagementModule::t('back', 'Are you sure you want to delete this user?'),
				'method' => 'post',
			    ],
			]) ?>
		    </p>

			<?= DetailView::widget([
				'model'      => $model,
				'attributes' => [
					'id',
					[
						'attribute'=>'status',
						'value'=>User::getStatusValue($model->status),
					],
					'username',
					'created_at:datetime',
					'updated_at:datetime',
				],
			]) ?>

		</div>
	</div>
</div>
