<?php
namespace webvimark\modules\UserManagement\components;

use webvimark\modules\UserManagement\models\User;
use yii\base\Security;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use Yii;

/**
 * Parent class for User model
 *
 * @property integer $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $confirmation_token
 * @property integer $status
 * @property integer $superadmin
 * @property integer $created_at
 * @property integer $updated_at
 */
abstract class UserIdentity extends ActiveRecord implements IdentityInterface
{
	/**
	 * @inheritdoc
	 */
	public static function findIdentity($id)
	{
		return static::findOne($id);
	}

	/**
	 * @inheritdoc
	 */
	public static function findIdentityByAccessToken($token, $type = null)
	{
		return static::findOne(['auth_key' => $token, 'status' => User::STATUS_ACTIVE]);
	}

	/**
	 * Finds user by username
	 *
	 * @param  string      $username
	 * @return static|null
	 */
	public static function findByUsername($username)
	{
		return static::findOne(['username' => $username, 'status' => User::STATUS_ACTIVE]);
	}

	/**
	 * Finds user by confirmation token
	 *
	 * @param  string      $token confirmation token
	 * @return static|null|User
	 */
	public static function findByConfirmationToken($token)
	{
		$expire    = Yii::$app->getModule('user-management')->confirmationTokenExpire;

		$parts     = explode('_', $token);
		$timestamp = (int)end($parts);

		if ( $timestamp + $expire < time() )
		{
			// token expired
			return null;
		}

		return static::findOne([
			'confirmation_token' => $token,
			'status'             => User::STATUS_ACTIVE,
		]);
	}

	/**
	 * Finds user by confirmation token
	 *
	 * @param  string      $token confirmation token
	 * @return static|null|User
	 */
	public static function findInactiveByConfirmationToken($token)
	{
		$expire    = Yii::$app->getModule('user-management')->confirmationTokenExpire;

		$parts     = explode('_', $token);
		$timestamp = (int)end($parts);

		if ( $timestamp + $expire < time() )
		{
			// token expired
			return null;
		}

		return static::findOne([
			'confirmation_token' => $token,
			'status'             => User::STATUS_INACTIVE,
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function getId()
	{
		return $this->getPrimaryKey();
	}

	/**
	 * @inheritdoc
	 */
	public function getAuthKey()
	{
		return $this->auth_key;
	}

	/**
	 * @inheritdoc
	 */
	public function validateAuthKey($authKey)
	{
		return $this->getAuthKey() === $authKey;
	}

	/**
	 * Validates password
	 *
	 * @param  string  $password password to validate
	 * @return boolean if password provided is valid for current user
	 */
	public function validatePassword($password)
	{
		return Yii::$app->security->validatePassword($password, $this->password_hash);
	}

	/**
	 * Generates password hash from password and sets it to the model
	 *
	 * @param string $password
	 */
	public function setPassword($password)
	{
		if ( php_sapi_name() == 'cli' )
		{
			$security = new Security();
			$this->password_hash = $security->generatePasswordHash($password);
		}
		else
		{
			$this->password_hash = Yii::$app->security->generatePasswordHash($password);
		}
	}

	/**
	 * Generates "remember me" authentication key
	 */
	public function generateAuthKey()
	{
		if ( php_sapi_name() == 'cli' )
		{
			$security = new Security();
			$this->auth_key = $security->generateRandomString();
		}
		else
		{
			$this->auth_key = Yii::$app->security->generateRandomString();
		}
	}

	/**
	 * Generates new confirmation token
	 */
	public function generateConfirmationToken()
	{
		$this->confirmation_token = Yii::$app->security->generateRandomString() . '_' . time();
	}

	/**
	 * Removes confirmation token
	 */
	public function removeConfirmationToken()
	{
		$this->confirmation_token = null;
	}
}