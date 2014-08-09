<?php
namespace app\webvimark\modules\UserManagement\models\rbacDB;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use Yii;


/**
 * @property integer $type
 * @property string $name
 * @property string $description
 * @property string $rule_name
 * @property string $data
 * @property integer $created_at
 * @property integer $updated_at
 */
abstract class AbstractItem extends ActiveRecord
{
	const TYPE_ROLE = 1;
	const TYPE_PERMISSION = 2;
	const TYPE_ROUTE = 3;

	/**
	 * Reassigned in child classes to type role, permission or route
	 */
	const ITEM_TYPE = 0;


	/**
	 * Useful helper for migrations and other stuff
	 *
	 * @param string      $name
	 * @param null|string $description
	 * @param null|string $ruleName
	 * @param null|string $data
	 *
	 * @return static
	 */
	public static function create($name, $description = null, $ruleName = null, $data = null)
	{
		$item = new static;

		$item->type = static::ITEM_TYPE;
		$item->name = $name;
		$item->description = ( $description === null AND static::ITEM_TYPE != static::TYPE_ROUTE ) ? $name : $description;
		$item->rule_name = $ruleName;
		$item->data = $data;

		$item->save();

		return $item;
	}

	/**
	 * @param mixed $condition
	 *
	 * @return bool
	 */
	public static function deleteIfExists($condition)
	{
		$model = static::findOne($condition);

		if ( $model )
		{
			$model->delete();
			return true;
		}

		return false;
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
	public static function tableName()
	{
		return 'auth_item';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['name', 'rule_name'], 'filter', 'filter' => 'trim'],

			['name', 'required'],
			['name', 'unique'],
			[['name', 'rule_name'], 'string', 'max' => 64],

			['rule_name', 'default', 'value'=>null],

			[['description', 'data'], 'safe'],

			['type', 'integer'],
			['type', 'in', 'range'=>[static::TYPE_ROLE, static::TYPE_PERMISSION, static::TYPE_ROUTE]],
		];
	}

	/**
	 * @inheritdoc
	 */
	public static function find()
	{
		return parent::find()->andWhere(['auth_item.type'=>static::ITEM_TYPE]);
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'name' => 'Название',
			'description' => 'Описание',
			'rule_name' => 'Правило',
			'data' => 'Данные',
			'type' => 'Тип',
			'created_at' => 'Создано',
			'updated_at' => 'Обновлено',
		];
	}

	/**
	 * Ensure type of item
	 *
	 * @inheritdoc
	 */
	public function beforeSave($insert)
	{
		$this->type = static::ITEM_TYPE;

		return parent::beforeSave($insert);
	}
} 