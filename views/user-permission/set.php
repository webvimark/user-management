<?php
/**
 * @var yii\web\View $this
 * @var app\webvimark\modules\UserManagement\models\User $user
 */

use app\webvimark\modules\UserManagement\components\AuthHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->title = 'Права для пользователя: ' . $user->username;

$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['/user-management/user/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
	<div class="col-sm-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<strong>
					<span class="glyphicon glyphicon-th"></span> Роли
				</strong>
			</div>
			<div class="panel-body">

				<?= Html::beginForm(['set-roles', 'id'=>$user->id]) ?>

				<?= Html::checkboxList(
					'roles',
					ArrayHelper::map(Yii::$app->authManager->getRolesByUser($user->id), 'name', 'name'),
					ArrayHelper::map(Yii::$app->authManager->getRoles(), 'name', 'name')
				) ?>
				<br/>

				<?= Html::submitButton(
					'<span class="glyphicon glyphicon-ok"></span> Сохранить',
					['class'=>'btn btn-primary btn-sm']
				) ?>

				<?= Html::endForm() ?>
			</div>
		</div>
	</div>

	<div class="col-sm-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<strong>
					<span class="glyphicon glyphicon-th"></span> Права
				</strong>
			</div>
			<div class="panel-body">

				<ul>
					<?php foreach (AuthHelper::separateRoutesAndPermissions(Yii::$app->authManager->getPermissionsByUser($user->id))->permissions as $permission): ?>
						<li><?= $permission->description ?></li>
					<?php endforeach ?>
				</ul>
			</div>
		</div>
	</div>
</div>