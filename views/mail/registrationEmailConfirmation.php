<?php
/**
 * @var $this yii\web\View
 * @var $user wpler\modules\UserManagement\models\User
 */
use yii\helpers\Html;
use wpler\modules\UserManagement\UserManagementModule;

?>
<?php
$returnUrl = Yii::$app->user->returnUrl == Yii::$app->homeUrl ? null : rtrim(Yii::$app->homeUrl, '/') . Yii::$app->user->returnUrl;

$confirmLink = Yii::$app->urlManager->createAbsoluteUrl(['/user-management/auth/confirm-registration-email', 'token' => $user->confirmation_token, 'returnUrl'=>$returnUrl]);
?>

<?= UserManagementModule::t('front','Hello, you have been registered on {hostinfo}',array('hostinfo'=>Yii::$app->urlManager->hostInfo)); ?>

<br/><br/>
<?= UserManagementModule::t('front','Follow this link to confirm your E-mail address and activate your account:'); ?>

<?= Html::a(UserManagementModule::t('front','Confirm E-mail'), $confirmLink) ?>