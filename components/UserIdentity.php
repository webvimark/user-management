<?php
namespace webvimark\modules\UserManagement\components;

use webvimark\modules\UserManagement\models\rbacDB\Route;
use Exception;
use webvimark\helpers\Singleton;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\helpers\Url;
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
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 0;

	/**
	 * Used when user want to change his own password
	 *
	 * @var string
	 */
	public $current_password;

	/**
	 * @var string
	 */
	public $password;

	/**
	 * @var string
	 */
	public $repeat_password;

	/**
	 * Store result in singleton to prevent multiple db requests with multiple calls
	 *
	 * @param bool $fromSingleton
	 *
	 * @return mixed|static
	 */
	public static function getCurrentUser($fromSingleton = true)
	{
		if ( !$fromSingleton )
		{
			return static::findOne(Yii::$app->user->id);
		}

		$user = Singleton::getData('__currentUser');

		if ( !$user )
		{
			$user = static::findOne(Yii::$app->user->id);

			Singleton::setData('__currentUser', $user);
		}

		return $user;
	}

	/**
	 * @param string $role
	 * @param bool   $superAdminAllowed
	 * @param bool   $searchInChildRoles
	 *
	 * @return bool
	 */
	public static function hasRole($role, $superAdminAllowed = true, $searchInChildRoles = false)
	{
		if ( $superAdminAllowed AND Yii::$app->user->isSuperadmin )
		{
			return true;
		}

		$cachedRoles = $searchInChildRoles ? Yii::$app->session->get('__userRolesWithChildren',[])
			: Yii::$app->session->get('__userRoles',[]);

		return in_array($role, $cachedRoles);
	}

	/**
	 * @param string $permission
	 * @param bool   $superAdminAllowed
	 *
	 * @return bool
	 */
	public static function hasPermission($permission, $superAdminAllowed = true)
	{
		if ( $superAdminAllowed AND Yii::$app->user->isSuperadmin )
		{
			return true;
		}

		return in_array($permission, Yii::$app->session->get('__userPermissions',[]));
	}

	/**
	 * Useful for Menu widget
	 *
	 * <example>
	 * 	...
	 * 		[ 'label'=>'Some label', 'url'=>['/site/index'], 'visible'=>User::canRoute(['/site/index']) ]
	 * 	...
	 * </example>
	 *
	 * @param string|array $route
	 * @param bool         $superAdminAllowed
	 *
	 * @return bool
	 */
	public static function canRoute($route, $superAdminAllowed = true)
	{
		if ( $superAdminAllowed AND Yii::$app->user->isSuperadmin )
		{
			return true;
		}

		$routeWithParams = explode('?', Url::to($route));

		$baseRoute = AuthHelper::unifyRoute($routeWithParams[0]);

		if ( Route::isFreeAccess($baseRoute) )
		{
			return true;
		}

		return in_array($baseRoute, Yii::$app->session->get('__userRoutes',[]));
	}

	/**
	 * Assign any RBAC item (role, permission, route) to user
	 *
	 * @param string $itemName
	 * @param bool   $hideErrors
	 *
	 * @throws \Exception
	 */
	public function assignRbacItem($itemName, $hideErrors = true)
	{
		try
		{
			Yii::$app->db->createCommand()->insert('auth_assignment', [
					'item_name' => $itemName,
					'user_id'   => $this->id,
				])->execute();
		}
		catch (Exception $e)
		{
			if ( !$hideErrors )
				throw $e;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			['password', 'required', 'on'=>['register', 'newUser', 'changePassword', 'changeOwnPassword', 'login']],
			['password', 'string', 'max' => 255],
			[['password', 'username'], 'filter', 'filter' => 'trim'],

			['repeat_password', 'required', 'on'=>['register', 'newUser', 'changePassword', 'changeOwnPassword']],
			['repeat_password', 'compare', 'compareAttribute'=>'password'],

			['current_password', 'required', 'on'=>'changeOwnPassword'],
			['current_password', 'validatePassword', 'on'=>'changeOwnPassword'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'password'=>'Пароль',
			'repeat_password'=>'Повторите пароль',
			'current_password'=>'Текущией пароль',
		];
	}

	/**
	 * getStatusList
	 * @return array
	 */
	public static function getStatusList()
	{
		return array(
			self::STATUS_ACTIVE => 'Активен',
			self::STATUS_INACTIVE => 'Нективен',
		);
	}

	/**
	 * getStatusValue
	 *
	 * @param string $val
	 *
	 * @return string
	 */
	public static function getStatusValue($val)
	{
		$ar = self::getStatusList();

		return isset( $ar[$val] ) ? $ar[$val] : $val;
	}

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
		throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
	}

	/**
	 * Finds user by username
	 *
	 * @param  string      $username
	 * @return static|null
	 */
	public static function findByUsername($username)
	{
		return static::findOne(['username' => $username, 'status' => static::STATUS_ACTIVE]);
	}

	/**
	 * Finds user by confirmation token
	 *
	 * @param  string      $token confirmation token
	 * @return static|null
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
			'status'             => static::STATUS_ACTIVE,
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
		$this->password_hash = Yii::$app->security->generatePasswordHash($password);
	}

	/**
	 * Generates "remember me" authentication key
	 */
	public function generateAuthKey()
	{
		$this->auth_key = Yii::$app->security->generateRandomString();
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

	/**
	 * @inheritdoc
	 */
	public function beforeSave($insert)
	{
		if ( $insert )
		{
			$this->generateAuthKey();
		}
		else
		{
			// Console doesn't have Yii::$app->user, so we skip it for console
			if ( php_sapi_name() != 'cli' )
			{
				if ( Yii::$app->user->id == $this->id )
				{
					// Make sure user will not deactivate himself
					$this->status = static::STATUS_ACTIVE;

					// Superadmin could not demote himself
					if ( Yii::$app->user->isSuperadmin AND $this->superadmin != 1 )
					{
						$this->superadmin = 1;
					}
				}

				// Don't let non-superadmin edit superadmin
				if ( !Yii::$app->user->isSuperadmin AND $this->oldAttributes['superadmin'] == 1 )
				{
					return false;
				}
			}
		}

		// If password has been set, than create password hash
		if ( $this->password )
		{
			$this->setPassword($this->password);
		}

		return parent::beforeSave($insert);
	}

	/**
	 * @inheritdoc
	 */
	public function beforeDelete()
	{
		// Console doesn't have Yii::$app->user, so we skip it for console
		if ( php_sapi_name() != 'cli' )
		{
			// Don't let delete yourself
			if ( Yii::$app->user->id == $this->id )
			{
				return false;
			}

			// Don't let non-superadmin delete superadmin
			if ( !Yii::$app->user->isSuperadmin AND $this->superadmin == 1 )
			{
				return false;
			}
		}

		return parent::beforeDelete();
	}
} 