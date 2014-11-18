<?php

namespace webvimark\modules\UserManagement\models;

use webvimark\helpers\LittleBigHelper;
use webvimark\modules\UserManagement\components\UserIdentity;
use webvimark\modules\UserManagement\models\rbacDB\Role;
use webvimark\modules\UserManagement\UserManagementModule;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $confirmation_token
 * @property string $bind_to_ip
 * @property string $registration_ip
 * @property integer $status
 * @property integer $superadmin
 * @property integer $created_at
 * @property integer $updated_at
 */
class User extends UserIdentity
{
	/**
	 * @var string
	 */
	public $gridRoleSearch;

	/**
	* @inheritdoc
	*/
	public static function tableName()
	{
		return 'user';
	}

	/**
	* @inheritdoc
	*/
	public function behaviors()
	{
		return [
			TimestampBehavior::className(),
		];
	}

	/**
	* @inheritdoc
	*/
	public function rules()
	{
		return [
			['username', 'required'],
			['username', 'unique'],

			['bind_to_ip', 'validateBindToIp'],
			['bind_to_ip', 'filter', 'filter'=>'trim'],

			[['username', 'bind_to_ip'], 'string', 'max' => 255],

			['password', 'required', 'on'=>['register', 'newUser', 'changePassword', 'changeOwnPassword', 'login']],
			['password', 'string', 'max' => 255],
			[['password', 'username'], 'filter', 'filter' => 'trim'],

			['repeat_password', 'required', 'on'=>['register', 'newUser', 'changePassword', 'changeOwnPassword']],
			['repeat_password', 'compare', 'compareAttribute'=>'password'],

			['current_password', 'required', 'on'=>'changeOwnPassword'],
			['current_password', 'validateCurrentPassword', 'on'=>'changeOwnPassword'],
		];
	}

	/**
	 * Validates current password
	 */
	public function validateCurrentPassword()
	{
		if ( !Yii::$app->security->validatePassword($this->current_password, $this->password_hash) )
		{
			$this->addError('current_password', UserManagementModule::t('back', "Wrong password"));
		}
	}

	/**
	 * Validate bind_to_ip attr to be in correct format
	 */
	public function validateBindToIp()
	{
		if ( $this->bind_to_ip )
		{
			$ips = explode(',', $this->bind_to_ip);

			foreach ($ips as $ip)
			{
				if ( !filter_var(trim($ip), FILTER_VALIDATE_IP) )
				{
					$this->addError('bind_to_ip', UserManagementModule::t('back', "Wrong format. Enter valid IPs separated by comma"));
				}
			}
		}
	}

	/**
	* @inheritdoc
	*/
	public function attributeLabels()
	{
		return ArrayHelper::merge(parent::attributeLabels(), [
			'id'                 => 'ID',
			'username'           => UserManagementModule::t('back', 'Login'),
			'superadmin'         => UserManagementModule::t('back', 'Superadmin'),
			'confirmation_token' => 'Confirmation Token',
			'status'             => UserManagementModule::t('back', 'Status'),
			'gridRoleSearch'     => UserManagementModule::t('back', 'Roles'),
			'created_at'         => UserManagementModule::t('back', 'Created'),
			'updated_at'         => UserManagementModule::t('back', 'Updated'),
		]);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getRoles()
	{
		return $this->hasMany(Role::className(), ['name' => 'item_name'])
			->viaTable('auth_assignment', ['user_id'=>'id']);
	}


	/**
	 * Make sure user will not deactivate himself and superadmin could not demote himself
	 * Also don't let non-superadmin edit superadmin
	 *
	 * @inheritdoc
	 */
	public function beforeSave($insert)
	{
		if ( $insert )
		{
			$this->registration_ip = LittleBigHelper::getRealIp();

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
	 * Don't let delete yourself and don't let non-superadmin delete superadmin
	 *
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
