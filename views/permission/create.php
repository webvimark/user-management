<?php
/**
 *
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var leo\modules\UserManagement\models\rbacDB\Permission $model
 */

use leo\modules\UserManagement\UserManagementModule;

$this->title = UserManagementModule::t('back', 'Permission creation');
$this->params['breadcrumbs'][] = ['label' => UserManagementModule::t('back', 'Permissions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<h2 class="lte-hide-title"><?= $this->title ?></h2>

<div class="panel panel-default">
	<div class="panel-body">
		<?= $this->render('_form', [
			'model'=>$model,
		]) ?>
	</div>
</div>