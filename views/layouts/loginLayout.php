<?php
use app\assets\AppAsset;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\bootstrap\BootstrapAsset;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */

$this->title = UserManagementModule::t('front', 'Authorization');
BootstrapAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
	<meta charset="<?= Yii::$app->charset ?>"/>
	<meta name="robots" content="noindex, nofollow">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?= Html::csrfMetaTags() ?>
	<title><?= Html::encode($this->title) ?></title>
	<?php $this->head() ?>
</head>
<body>

<?php $this->beginBody() ?>

<?= $content ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>