<?php
/**
 * @var $this yii\web\View
 * @var $user webvimark\modules\UserManagement\models\User
 */
use yii\helpers\Html;

?>
<?php
$returnUrl = Yii::$app->user->returnUrl == Yii::$app->homeUrl ? null : rtrim(Yii::$app->homeUrl, '/') . Yii::$app->user->returnUrl;

$confirmLink = Yii::$app->urlManager->createAbsoluteUrl(['/user-management/auth/confirm-registration-email', 'token' => $user->confirmation_token, 'returnUrl'=>$returnUrl]);
?>

Hello, you have been registered on <?= Yii::$app->urlManager->hostInfo ?>

<br/><br/>
Follow this link to confirm your E-mail and activate account:

<?= Html::a('confirm E-mail', $confirmLink) ?>