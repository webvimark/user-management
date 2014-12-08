<?php

use webvimark\modules\UserManagement\UserManagementModule;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var webvimark\modules\UserManagement\models\forms\ConfirmEmailForm $model
 */

$this->title = UserManagementModule::t('front', 'Confirm E-mail');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="confirm-email">

	<h2 class="lte-hide-title"><?= $this->title ?></h2>

	<div class="panel panel-default">
		<div class="panel-body">

			<?php if ( Yii::$app->session->hasFlash('error') ): ?>
				<div class="alert alert-warning text-center">
					<?= Yii::$app->session->getFlash('error') ?>
				</div>
			<?php endif; ?>

			<?php if ( $model->user->confirmation_token === null ): ?>

				<?php $form = ActiveForm::begin([
					'id'=>'user',
					'layout'=>'horizontal',
					'validateOnBlur'=>false,
				]); ?>

				<?= $form->field($model, 'email')->textInput(['maxlength' => 255, 'autofocus'=>true]) ?>

				<div class="form-group">
					<div class="col-sm-offset-3 col-sm-9">
						<?= Html::submitButton(
							'<span class="glyphicon glyphicon-ok"></span> ' . UserManagementModule::t('front', 'Confirm'),
							['class' => 'btn btn-primary']
						) ?>
					</div>
				</div>

				<?php ActiveForm::end(); ?>
			<?php else: ?>

				<div class="alert alert-info text-center">
					<?= UserManagementModule::t('back', 'E-mail with activation link has been sent to <b>{email}</b>. This link will expire in {minutes} min.', [
						'email'=>$model->user->email,
						'minutes'=>$model->getTokenTimeLeft(true),
					]) ?>
				</div>
			<?php endif; ?>


		</div>
	</div>
</div>
