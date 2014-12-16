<?php

namespace webvimark\modules\UserManagement\models\rbacDB;

use webvimark\modules\UserManagement\UserManagementModule;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "auth_item_group".
 *
 * @property string $code
 * @property string $name
 * @property integer $created_at
 * @property integer $updated_at
 */
class AuthItemGroup extends \yii\db\ActiveRecord
{
	/**
	* @inheritdoc
	*/
	public static function tableName()
	{
		return Yii::$app->getModule('user-management')->auth_item_group_table;
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
			[['code', 'name'], 'required'],
			['code', 'unique'],
			[['code'], 'string', 'max' => 64],
			[['code', 'name'], 'trim'],
			[['name'], 'string', 'max' => 255],
		];
	}

	/**
	* @inheritdoc
	*/
	public function attributeLabels()
	{
		return [
			'name'        => UserManagementModule::t('back', 'Name'),
			'code'        => UserManagementModule::t('back', 'Code'),
			'created_at'  => UserManagementModule::t('back', 'Created'),
			'updated_at'  => UserManagementModule::t('back', 'Updated'),
		];
	}
}
