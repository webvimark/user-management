<?php

use webvimark\modules\UserManagement\models\User;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use webvimark\extensions\BootstrapSwitch\BootstrapSwitch;

/**
 * @var yii\web\View $this
 * @var webvimark\modules\UserManagement\models\User $model
 * @var yii\bootstrap\ActiveForm $form
 */
?>

<div class="user-form">

	<?php $form = ActiveForm::begin([
		'id'=>'user',
		'layout'=>'horizontal',
		'validateOnBlur' => false,
	]); ?>

	<?= $form->field($model->loadDefaultValues(), 'status')
		->dropDownList(User::getStatusList()) ?>

	<?= $form->field($model, 'username')->textInput(['maxlength' => 255, 'autocomplete'=>'off']) ?>

	<?php if ( $model->isNewRecord ): ?>

		<?= $form->field($model, 'password')->passwordInput(['maxlength' => 255, 'autocomplete'=>'off']) ?>

		<?= $form->field($model, 'repeat_password')->passwordInput(['maxlength' => 255, 'autocomplete'=>'off']) ?>
	<?php endif; ?>


	<?php if ( User::hasPermission('bindUserToIp') ): ?>

		<?= $form->field($model, 'bind_to_ip')
			->textInput(['maxlength' => 255])
			->hint(UserManagementModule::t('back','For example: 123.34.56.78, 168.111.192.12')) ?>

	<?php endif; ?>

	<?php if ( User::hasPermission('editUserEmail') ): ?>

		<?= $form->field($model, 'email')->textInput(['maxlength' => 255]) ?>
		<?= $form->field($model, 'email_confirmed')->checkbox() ?>

	<?php endif; ?>

    <div class="form-group">
		<label class="control-label col-sm-3" id="password" for="token">Access Token</label>
		<div class="col-sm-6">                       
        	<input type="password" size="20" class="form-control" id="token" readonly="true" value="<?=$model->access_token ?>">

        </div>
	</div>

	<div class="form-group">
        <div class="col-sm-6 col-sm-offset-3">
        	<div class="checkbox">
	            <label for="show">
		            <input id="show" type="checkbox">
	                <?=Yii::t('app','Show Token') ?>
	            </label>
        	</div>
        </div>
    </div>

	<div class="form-group">
		<div class="col-sm-offset-3">
			<div class="col-sm-3">
				
			<?php if ( $model->isNewRecord ): ?>
				<?= Html::submitButton(
					'<span class="glyphicon glyphicon-plus-sign"></span> ' . UserManagementModule::t('back', 'Create'),
					['class' => 'btn btn-success']
				) ?>
			<?php else: ?>
				<?= Html::submitButton(
					'<span class="glyphicon glyphicon-ok"></span> ' . UserManagementModule::t('back', 'Save'),
					['class' => 'btn btn-primary']
				) ?>
			<?php endif; ?>
			</div>
			<div class="col-sm-5 text-right">
		        <button id="copy" class="btn btn-info"><?=Yii::t('app','Copy Token') ?></button>
		        <button id="generate" class="btn btn-success"><?=Yii::t('app','Generate Token') ?></button>
			</div>
	    </div>
	</div>

	<?php ActiveForm::end(); ?>

</div>

<?php BootstrapSwitch::widget() ?>


<?php  

$this->registerJs('

    $(\'#generate\').click(function(){

        if (confirm(\'Deseja gerar um novo Token?\')) {
            $.ajax
            ({ 
                url: \''.Url::toRoute(['user/generate-access-token']).'\',
                type: \'GET\',
                data: {userId : '.$model->id.'},
                success: function(result)
                {
                    return true;
                }
            });
        }
    });        

    $(\'#copy\').click(function(){
    	event.preventDefault();
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($("#token").val()).select();
        document.execCommand("copy");
        alert("'.Yii::t('app','Token copied to clipboard').'")
        $temp.remove();
    });

    $("#show").on("ifChanged", function(event){
        if(this.checked){
            $("#token").prop("type", "text");
        } else {
            $("#token").prop("type", "password");
        }
    });
');

?>