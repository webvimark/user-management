<?php
/**
 * @var $this yii\web\View
 * @var $user webvimark\modules\UserManagement\models\User
 */
use yii\helpers\Html;

?>
<?php
$confirmLink = Yii::$app->urlManager->createAbsoluteUrl(['/user-management/auth/confirm-registration-email', 'token' => $user->confirmation_token]);
?>

Hello, you have been registered on <?= Yii::$app->urlManager->hostInfo ?>

Follow this link To confirm your E-mail and  activate account:

<?= Html::a('Confirm E-mail', $confirmLink) ?>