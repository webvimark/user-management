<?php

namespace webvimark\modules\UserManagement\models;

use Browser;
use webvimark\helpers\LittleBigHelper;
use Yii;

/**
 * This is the model class for table "user_visit_log".
 *
 * @property integer $id
 * @property string $token
 * @property string $ip
 * @property string $language
 * @property string $browser_and_os
 * @property integer $user_id
 * @property integer $visit_time
 *
 * @property User $user
 */
class UserVisitLog extends \webvimark\components\BaseActiveRecord
{
	CONST SESSION_TOKEN = '__visitorToken';

	/**
	 * Save new record in DB and write unique token in session
	 *
	 * @param int $userId
	 */
	public static function newVisitor($userId)
	{
		$browser = new Browser();
		$userAgent = array(
			'platform' => $browser->getPlatform(),
			'browser'  => $browser->getBrowser(),
			'version'  => $browser->getVersion(),
			'summary'  => $browser->getUserAgent(),
		);

		$model                 = new self();
		$model->user_id        = $userId;
		$model->token          = uniqid();
		$model->ip             = LittleBigHelper::getRealIp();
		$model->language       = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
		$model->browser_and_os = print_r($userAgent, true);
		$model->visit_time     = time();
		$model->save(false);

		Yii::$app->session->set(self::SESSION_TOKEN, $model->token);
	}

	/**
	 * Checks if token stored in session is equal to token from this user last visit
	 * Logout if not
	 */
	public static function checkToken()
	{
		if (Yii::$app->user->isGuest)
			return;

		$model = static::find()
			->andWhere(['user_id'=>Yii::$app->user->id])
			->orderBy('id DESC')
			->asArray()
			->one();

		if ( !$model OR ($model['token'] !== Yii::$app->session->get(self::SESSION_TOKEN)) )
		{
			Yii::$app->user->logout();

			echo "<script> location.reload();</script>";
			Yii::$app->end();
		}
	}

	/**
	* @inheritdoc
	*/
	public static function tableName()
	{
		return 'user_visit_log';
	}

	/**
	* @inheritdoc
	*/
	public function rules()
	{
		return [
			[['token', 'ip', 'language', 'browser_and_os', 'visit_time'], 'required'],
			[['user_id', 'visit_time'], 'integer'],
			[['token', 'browser_and_os'], 'string', 'max' => 255],
			[['ip'], 'string', 'max' => 15],
			[['language'], 'string', 'max' => 2]
		];
	}

	/**
	* @inheritdoc
	*/
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'token' => 'Token',
			'ip' => 'Ip',
			'language' => 'Language',
			'browser_and_os' => 'Browser And Os',
			'user_id' => 'User',
			'visit_time' => 'Visit Time',
		];
	}

	/**
	* @return \yii\db\ActiveQuery
	*/
	public function getUser()
	{
		return $this->hasOne(User::className(), ['id' => 'user_id']);
	}
}
