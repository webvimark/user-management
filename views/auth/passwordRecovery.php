<?php

use webvimark\modules\UserManagement\UserManagementModule;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var yii\base\DynamicModel $model
 */

$this->title = UserManagementModule::t('front', 'Password recovery ');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="password-recovery">

	<?php $form = ActiveForm::begin([
		'id'=>'user',
		'layout'=>'horizontal',
	]); ?>

	<?= $form->field($model, 'email')->textInput(['maxlength' => 255, 'autofocus'=>true]) ?>

	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-9">
			<?= Html::submitButton(
				'<span class="glyphicon glyphicon-ok"></span> ' . UserManagementModule::t('front', 'Recover'),
				['class' => 'btn btn-primary']
			) ?>
		</div>
	</div>

	<?php ActiveForm::end(); ?>

</div>
