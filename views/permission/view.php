<?php
/**
 * @var $this yii\web\View
 * @var yii\widgets\ActiveForm $form
 * @var array $routes
 * @var array $childRoutes
 * @var array $permissions
 * @var array $childPermissions
 * @var yii\rbac\Permission $item
 */
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
$this->title = $item->name;
$this->params['breadcrumbs'][] = ['label' => 'Права', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<h2>Настройки для правила: <b><?= $this->title ?></b></h2>
<br/>


<div class="row">
	<div class="col-sm-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<strong>
					<span class="glyphicon glyphicon-th"></span> Дочерние права
				</strong>
			</div>
			<div class="panel-body">

				<?= Html::beginForm(['set-child-permissions', 'id'=>$item->name]) ?>

				<?= Html::checkboxList(
					'child_permissions',
					ArrayHelper::map($childPermissions, 'name', 'name'),
					ArrayHelper::map($permissions, 'name', 'description'),
					['separator'=>'<br>']
				) ?>

				<hr/>
				<?= Html::submitButton(
					'<span class="glyphicon glyphicon-ok"></span> Сохранить',
					['class'=>'btn btn-primary btn-sm']
				) ?>

				<?= Html::endForm() ?>
			</div>
		</div>
	</div>

	<div class="col-sm-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<strong>
					<span class="glyphicon glyphicon-th"></span> Routes

					<?= Html::a(
						'Refresh routes',
						['refresh-routes', 'id'=>$item->name],
						[
							'class' => 'btn btn-default btn-sm pull-right',
							'style'=>'margin-top:-5px',
						]
					) ?>

				</strong>
			</div>

			<div class="panel-body">

				<?= Html::beginForm(['set-child-routes', 'id'=>$item->name]) ?>

				<div class="row">
					<div class="col-sm-3">
						<?= Html::submitButton(
							'<span class="glyphicon glyphicon-ok"></span> Сохранить',
							['class'=>'btn btn-primary btn-sm']
						) ?>
					</div>

					<div class="col-sm-6">
						<input id="search-in-routes" autofocus="on" type="text" class="form-control input-sm" placeholder="Search route">
					</div>

					<div class="col-sm-3 text-right">
						<span id="show-only-selected-routes" class="btn btn-default btn-sm">
							<i class="fa fa-minus"></i> Show only selected
						</span>
						<span id="show-all-routes" class="btn btn-default btn-sm hide">
							<i class="fa fa-plus"></i> Show all
						</span>

					</div>
				</div>

				<hr/>

				<?= Html::checkboxList(
					'child_routes',
					ArrayHelper::map($childRoutes, 'name', 'name'),
					ArrayHelper::map($routes, 'name', 'name'),
					[
						'id'=>'routes-list',
						'separator'=>'<div class="separator"></div>',
//						'item'=>function ($index, $label, $name, $checked, $value){
//								return $index;
//							},
					]
				) ?>

				<hr/>
				<?= Html::submitButton(
					'<span class="glyphicon glyphicon-ok"></span> Сохранить',
					['class'=>'btn btn-primary btn-sm']
				) ?>

				<?= Html::endForm() ?>

			</div>
		</div>
	</div>
</div>

<?php
$js = <<<JS

function showAllRoutesBack() {
	$('#routes-list').find('.hide').each(function(){
		$(this).removeClass('hide');
	});
}

// Hide on not selected routes
$('#show-only-selected-routes').on('click', function(){
	$(this).addClass('hide');
	$('#show-all-routes').removeClass('hide');

	$('#routes-list').find('input[type="checkbox"]').each(function(){
		var _t = $(this);

		if ( ! _t.is(':checked') )
		{
			_t.closest('label').addClass('hide');
			_t.closest('div.separator').addClass('hide');
		}
	});
});

// Show all routes back
$('#show-all-routes').on('click', function(){
	$(this).addClass('hide');
	$('#show-only-selected-routes').removeClass('hide');

	showAllRoutesBack();
});

// Search in routes and hide not matched
$('#search-in-routes').on('change keyup', function(){
	var input = $(this);

	if ( input.val() == '' )
	{
		showAllRoutesBack();
		return;
	}

	$('#routes-list').find('label').each(function(){
		var _t = $(this);

		if ( _t.html().indexOf(input.val()) > -1 )
		{
			_t.closest('label').removeClass('hide');
			_t.closest('div.separator').removeClass('hide');
		}
		else
		{
			_t.closest('label').addClass('hide');
			_t.closest('div.separator').addClass('hide');
		}
	});
});

JS;

$this->registerJs($js);
?>