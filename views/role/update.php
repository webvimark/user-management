<?php
/**
 * @var yii\widgets\ActiveForm $form
 * @var webvimark\modules\UserManagement\forms\ItemForm $model
 */

$this->title = 'Редактирование роли: ' . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Роли', 'url' => ['index']];
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
			'insert'=>false,
		]) ?>
	</div>
</div>