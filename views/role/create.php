<?php
/**
 *
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var webvimark\modules\UserManagement\models\rbacDB\Role $model
 */
use webvimark\modules\UserManagement\UserManagementModule;

$this->title = UserManagementModule::t('back', 'Role creation');
$this->params['breadcrumbs'][] = ['label' => UserManagementModule::t('back', 'Roles'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="panel panel-default">
	<div class="panel-heading">
		<strong>
			<span class="glyphicon glyphicon-th"></span> <?= $this->title ?>
		</strong>
	</div>
	<div class="panel-body">
		<?= $this->render('_form', [
			'model'=>$model,
		]) ?>
	</div>
</div>