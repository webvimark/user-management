<?php
/**
 * @var yii\widgets\ActiveForm $form
 * @var array $childRoles
 * @var array $allRoles
 * @var array $routes
 * @var array $currentRoutes
 * @var array $permissions
 * @var array $currentPermissions
 * @var yii\rbac\Role $role
 */

use webvimark\modules\UserManagement\UserManagementModule;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->title = $role->name;
$this->params['breadcrumbs'][] = ['label' => UserManagementModule::t('back', 'Roles'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<h2><?= UserManagementModule::t('back', 'Permissions for role:') ?> <b><?= $this->title ?></b></h2>
<br/>

<div class="row">
	<div class="col-sm-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<strong>
					<span class="glyphicon glyphicon-th"></span> <?= UserManagementModule::t('back', 'Child roles') ?>
				</strong>
			</div>
			<div class="panel-body">
				<?= Html::beginForm(['set-child-roles', 'id'=>$role->name]) ?>

				<?= Html::checkboxList(
					'child_roles',
					ArrayHelper::map($childRoles, 'name', 'name'),
					ArrayHelper::map($allRoles, 'name', 'name'),
					['separator'=>'<br>']
				) ?>

				<hr/>
				<?= Html::submitButton(
					'<span class="glyphicon glyphicon-ok"></span> ' . UserManagementModule::t('back', 'Save'),
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
					<span class="glyphicon glyphicon-th"></span> <?= UserManagementModule::t('back', 'Permissions') ?>
				</strong>
			</div>
			<div class="panel-body">
				<?= Html::beginForm(['set-child-permissions', 'id'=>$role->name]) ?>

				<?= Html::checkboxList(
					'child_permissions',
					ArrayHelper::map($currentPermissions, 'name', 'name'),
					ArrayHelper::map($permissions, 'name', 'description'),
					['separator'=>'<br>']
				) ?>

				<hr/>
				<?= Html::submitButton(
					'<span class="glyphicon glyphicon-ok"></span> ' . UserManagementModule::t('back', 'Save'),
					['class'=>'btn btn-primary btn-sm']
				) ?>

				<?= Html::endForm() ?>

			</div>
		</div>
	</div>
</div>