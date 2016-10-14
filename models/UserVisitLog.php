<?php

namespace webvimark\modules\UserManagement\models;

use Ikimea\Browser\Browser;
use webvimark\helpers\LittleBigHelper;
use webvimark\modules\UserManagement\UserManagementModule;
use Yii;

/**
 * This is the model class for table "user_visit_log".
 *
 * @property integer $id
 * @property string $token
 * @property string $ip
 * @property string $language
 * @property string $browser
 * @property string $os
 * @property string $user_agent
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

		$model             = new self();
		$model->user_id    = $userId;
		$model->token      = uniqid();
		$model->ip         = LittleBigHelper::getRealIp();
		$model->language   = isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : '';
		$model->browser    = $browser->getBrowser();
		$model->os         = $browser->getPlatform();
		$model->user_agent = $browser->getUserAgent();
		$model->visit_time = time();
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
		return Yii::$app->getModule('user-management')->user_visit_log_table;
	}

	/**
	* @inheritdoc
	*/
	public function rules()
	{
		return [
			[['token', 'ip', 'language', 'visit_time'], 'required'],
			[['user_id', 'visit_time'], 'integer'],
			[['token', 'user_agent'], 'string', 'max' => 255],
			[['ip'], 'string', 'max' => 15],
			[['os'], 'string', 'max' => 20],
			[['browser'], 'string', 'max' => 30],
			[['language'], 'string', 'max' => 2]
		];
	}

	/**
	* @inheritdoc
	*/
	public function attributeLabels()
	{
		return [
			'id'         => UserManagementModule::t('back', 'ID'),
			'token'      => UserManagementModule::t('back', 'Token'),
			'ip'         => UserManagementModule::t('back', 'IP'),
			'language'   => UserManagementModule::t('back', 'Language'),
			'browser'    => UserManagementModule::t('back', 'Browser'),
			'os'         => UserManagementModule::t('back', 'OS'),
			'user_agent' => UserManagementModule::t('back', 'User agent'),
			'user_id'    => UserManagementModule::t('back', 'User'),
			'visit_time' => UserManagementModule::t('back', 'Visit Time'),
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
