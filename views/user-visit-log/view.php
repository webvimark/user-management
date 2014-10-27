<?php

use webvimark\modules\UserManagement\UserManagementModule;
use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var webvimark\modules\UserManagement\models\UserVisitLog $model
 */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => UserManagementModule::t('back', 'Visit log'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-visit-log-view">


	<div class="panel panel-default">
		<div class="panel-heading">
			<strong>
				<span class="glyphicon glyphicon-th"></span> <?= Html::encode($this->title) ?>
			</strong>
		</div>
		<div class="panel-body">

			<?= DetailView::widget([
				'model' => $model,
				'attributes' => [
					[
						'attribute'=>'user_id',
						'value'=>@$model->user->username,
					],
					'language',
					'ip',
					[
						'attribute'=>'browser_and_os',
						'value'=>'<pre>' . $model->browser_and_os . '</pre>',
						'format'=>'raw',
					],

					'visit_time:datetime',
				],
			]) ?>

		</div>
	</div>
</div>
