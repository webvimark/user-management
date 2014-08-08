<?php
/**
 * @var $this yii\web\View
 * @var $model app\webvimark\modules\UserManagement\models\LoginForm
 */
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>


<div class="container" id="login-wrapper">
	<div class="row">
		<div class="col-md-4 col-md-offset-4">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">Авторизация</h3>
				</div>
				<div class="panel-body">
					<fieldset>

						<?php $form = ActiveForm::begin([
							'id'      => 'login-form',
							'options'=>['autocomplete'=>'off'],
							'fieldConfig' => [
								'template'=>"{input}\n{error}",
							],
						]) ?>

						<?= $form->field($model, 'username')
							->textInput(['placeholder'=>$model->getAttributeLabel('username'), 'autocomplete'=>'off']) ?>

						<?= $form->field($model, 'password')
							->passwordInput(['placeholder'=>$model->getAttributeLabel('password'), 'autocomplete'=>'off']) ?>

						<?= $form->field($model, 'rememberMe')->checkbox() ?>

						<?= Html::submitButton('Войти', ['class' => 'btn btn-lg btn-success btn-block']) ?>

						<?php ActiveForm::end() ?>

					</fieldset>

				</div>
			</div>
		</div>
	</div>
</div>

<?php
$css = <<<CSS
body {
	position: relative;
}
#login-wrapper {
	position: relative;
	top: 30%;
}
CSS;

$this->registerCss($css);
?>

