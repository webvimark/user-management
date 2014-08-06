<?php

use app\webvimark\modules\UserManagement\models\User;
use webvimark\extensions\BootstrapSwitch\BootstrapSwitch;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\webvimark\modules\UserManagement\models\User $model
 */

$this->title = 'Редактирование пользователя: ' . ' ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->username, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Редактирование';
?>
<div class="user-update">

	<div class="panel panel-default">
		<div class="panel-heading">
			<strong>
				<span class="glyphicon glyphicon-th"></span> <?= Html::encode($this->title) ?>
			</strong>
		</div>
		<div class="panel-body">


			<div class="user-form">

				<?php $form = ActiveForm::begin([
					'id'=>'user',
					'layout'=>'horizontal',
				]); ?>

				<?= $form->field($model->loadDefaultValues(), 'status')
					->dropDownList(User::getStatusList()) ?>

				<?= $form->field($model, 'username')->textInput(['maxlength' => 255]) ?>


				<div class="form-group">
					<div class="col-sm-offset-3 col-sm-9">
						<?php if ( $model->isNewRecord ): ?>
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

				<?php ActiveForm::end(); ?>

			</div>

			<?php BootstrapSwitch::widget() ?>
		</div>
	</div>

</div>
