<?php
/**
 * @var yii\web\View $this
 * @var webvimark\modules\UserManagement\models\User $user
 */

use webvimark\modules\UserManagement\models\rbacDB\Role;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\bootstrap\BootstrapPluginAsset;
use yii\helpers\ArrayHelper;
use webvimark\modules\UserManagement\models\rbacDB\Permission;
use yii\helpers\Html;

BootstrapPluginAsset::register($this);
$this->title = UserManagementModule::t('back', 'Roles and permissions for user:') . ' ' . $user->username;

$this->params['breadcrumbs'][] = ['label' => UserManagementModule::t('back', 'Users'), 'url' => ['/user-management/user/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
	<div class="col-sm-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<strong>
					<span class="glyphicon glyphicon-th"></span> <?= UserManagementModule::t('back', 'Roles') ?>
				</strong>
			</div>
			<div class="panel-body">

				<?= Html::beginForm(['set-roles', 'id'=>$user->id]) ?>

				<?= Html::checkboxList(
					'roles',
					ArrayHelper::map(Role::getUserRoles($user->id), 'name', 'name'),
					ArrayHelper::map(Role::getAvailableRoles(), 'name', 'name'),
					[
						'item'=>function ($index, $label, $name, $checked, $value) {
								$list = '<ul style="padding-left: 10px">';
								foreach (Role::getPermissionsByRole($value) as $permissionName => $permissionDescription)
								{
									$list .= $permissionDescription ? "<li>{$permissionDescription}</li>" : "<li>{$permissionName}</li>";
								}
								$list .= '</ul>';

								$helpIcon = Html::beginTag('span', [
									'title'        => UserManagementModule::t('back', 'Permissions for role - "{role}"',[
											'role'=>$label,
										]),
									'data-content' => $list,
									'data-toggle'  => 'popover',
									'data-html'    => 'true',
									'role'         => 'button',
									'style'        => 'margin-bottom: 5px; padding: 0 5px',
									'class'        => 'btn btn-sm btn-default',
								]);
								$helpIcon .= '?';
								$helpIcon .= Html::endTag('span');

								$isChecked = $checked ? 'checked' : '';
								$checkbox = "<label><input type='checkbox' name='{$name}' value='{$value}' {$isChecked}> {$label}</label>";

								return $helpIcon . ' ' . $checkbox;
							},
						'separator'=>'<br>',
					]
				) ?>
				<br/>

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

				<ul>
					<?php foreach (Permission::getUserPermissions($user->id) as $permission): ?>
						<li><?= $permission->description ?></li>
					<?php endforeach ?>
				</ul>
			</div>
		</div>
	</div>
</div>

<?php
$this->registerJs(<<<JS
$('[data-toggle="popover"]').popover();
JS
);
?>