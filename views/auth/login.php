<?php
/**
 * @var $this yii\web\View
 * @var $model app\webvimark\modules\UserManagement\models\LoginForm
 */
use app\webvimark\modules\UserManagement\models\LoginForm;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

echo "<pre>";
var_dump(Yii::$app->user->id);
var_dump(Yii::$app->user->isGuest);
echo "</pre>";
?>

<?php $form = ActiveForm::begin([
	'id'      => 'login-form',
	'options' => ['class' => 'form-horizontal'],
]) ?>

<?= $form->field($model, 'username') ?>

<?= $form->field($model, 'password')->passwordInput() ?>

<?= $form->field($model, 'rememberMe')->checkbox() ?>

<div class="form-group">
	<div class="col-lg-offset-1 col-lg-11">
		<?= Html::submitButton('Login', ['class' => 'btn btn-primary']) ?>
	</div>
</div>
<?php ActiveForm::end() ?>
