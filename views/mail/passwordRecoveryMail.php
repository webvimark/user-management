<?php
/**
 * @var $this yii\web\View
 * @var $user wpler\modules\UserManagement\models\User
 */
use yii\helpers\Html;
use wpler\modules\UserManagement\UserManagementModule;

?>
<?php
$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['/user-management/auth/password-recovery-receive', 'token' => $user->confirmation_token]);
?>

<?= UserManagementModule::t('front','Hello {username}, follow this link to reset your password:',array('username'=>Html::encode($user->username))); ?>

<?= Html::a(UserManagementModule::t('front','Reset password'), $resetLink) ?>