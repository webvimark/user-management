<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var webvimark\modules\UserManagement\models\User $model
 */

$this->title = $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-view">
	<div class="panel panel-default">
		<div class="panel-heading">
			<strong>
				<span class="glyphicon glyphicon-th"></span> <?= Html::encode($this->title) ?>
			</strong>
		</div>
		<div class="panel-body">

		    <p>
			<?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary']) ?>
			<?= Html::a('Создать', ['create'], ['class' => 'btn btn-sm btn-success']) ?>
			<?= Html::a('Удалить', ['delete', 'id' => $model->id], [
			    'class' => 'btn btn-sm btn-danger pull-right',
			    'data' => [
				'confirm' => 'Вы уверены, что хотите удалить этот элемент?',
				'method' => 'post',
			    ],
			]) ?>
		    </p>

		    <?= DetailView::widget([
			'model' => $model,
			'attributes' => [
			    'id',
			    'status',
			    'username',
			    'created_at:datetime',
			    'updated_at:datetime',
			],
		    ]) ?>

		</div>
	</div>
</div>
