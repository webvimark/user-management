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



<?php
$css = <<<CSS
body {
	background: black;
}
canvas {
	display: block;
	position: absolute;
}
CSS;

$this->registerCss($css);


$js = <<<JS
var c = document.getElementById("c");
var ctx = c.getContext("2d");

//making the canvas full screen
c.height = window.innerHeight;
c.width = window.innerWidth;

//chinese characters - taken from the unicode charset
var chinese = "田由甲申甴电甶男甸甹町画甼甽甾甿畀畁畂畃畄畅畆畇畈畉畊畋界畍畎畏畐畑";
//converting the string into an array of single characters
chinese = chinese.split("");

var font_size = 10;
var columns = c.width/font_size; //number of columns for the rain
//an array of drops - one per column
var drops = [];
//x below is the x coordinate
//1 = y co-ordinate of the drop(same for every drop initially)
for(var x = 0; x < columns; x++)
	drops[x] = 1;

//drawing the characters
function draw()
{
	//Black BG for the canvas
	//translucent BG to show trail
	ctx.fillStyle = "rgba(0, 0, 0, 0.05)";
	ctx.fillRect(0, 0, c.width, c.height);

	ctx.fillStyle = "#0F0"; //green text
	ctx.font = font_size + "px arial";
	//looping over drops
	for(var i = 0; i < drops.length; i++)
	{
		//a random chinese character to print
		var text = chinese[Math.floor(Math.random()*chinese.length)];
		//x = i*font_size, y = value of drops[i]*font_size
		ctx.fillText(text, i*font_size, drops[i]*font_size);

		//sending the drop back to the top randomly after it has crossed the screen
		//adding a randomness to the reset to make the drops scattered on the Y axis
		if(drops[i]*font_size > c.height && Math.random() > 0.975)
			drops[i] = 0;

		//incrementing Y coordinate
		drops[i]++;
	}
}

setInterval(draw, 53);
JS;

$this->registerJs($js);

?>