<?php
/**
 * @var bool $insert
 * @var yii\widgets\ActiveForm $form
 * @var app\webvimark\modules\UserManagement\forms\ItemForm $model
 */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
?>

<?php $form = ActiveForm::begin([
	'id'      => 'role-form',
	'layout'=>'horizontal',
]) ?>

<?= $form->field($model, 'name')->textInput(['maxlength' => 255, 'autofocus'=>$model->isNewRecord ? true:false]) ?>

<?= $form->field($model, 'description')->textArea(['rows'=>5]) ?>


	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-9">
			<?php if ( $insert ): ?>
				<?= Html::submitButton(
					'<span class="glyphicon glyphicon-plus-sign"></span> Создать',
					['class' => 'btn btn-success']
				) ?>
			<?php else: ?>
				<?= Html::submitButton(
					'<span class="glyphicon glyphicon-ok"></span> Сохранить',
					['class' => 'btn btn-primary']
				) ?>
			<?php endif; ?>
		</div>
	</div>
<?php ActiveForm::end() ?>