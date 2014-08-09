<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 6/11/14
 * Time: 5:50 PM
 */

namespace webvimark\modules\UserManagement\forms;


use yii\base\Exception;
use yii\base\Model;
use yii\rbac\Role;
use yii\rbac\Permission;

class ItemForm extends Model
{
	const TYPE_ROLE = 1;
	const TYPE_PERMISSION = 2;

	public $name;
	public $oldName;
	public $description;
	public $ruleName;
	public $data;

	private $_type;

	/**
	 * @param string $action - "add" or "update"
	 *
	 * @throws \InvalidArgumentException
	 * @return bool
	 */
	public function configureItem($action = 'add')
	{
		try
		{
			if ( $this->_type == self::TYPE_ROLE )
			{
				$item = \Yii::$app->authManager->createRole($this->name);
			}
			elseif ($this->_type == self::TYPE_PERMISSION)
			{
				$item = \Yii::$app->authManager->createPermission($this->name);
			}
			else
			{
				throw new \InvalidArgumentException(\Yii::t('auth', "ItemForm has to have Role or Permission type"));
			}

			$this->fillFields($this, $item);

			if ( $action == 'add' )
			{
				\Yii::$app->authManager->add($item);
			}
			else
			{
				\Yii::$app->authManager->update($this->oldName, $item);
			}

			return true;
		}
		catch (Exception $e)
		{
			$errorMessage = ($e->getCode() == 23000) ? 'You already have role or permission with this name' : $e->getMessage();

			$this->addError('name', $errorMessage);

			return false;
		}

	}

	/**
	 * @param Role|Permission|ItemForm $from
	 * @param Role|Permission|ItemForm $to
	 */
	public function fillFields($from, $to)
	{
		$to->name = $from->name;
		$to->description = $from->description;
		$to->ruleName = $from->ruleName;
		$to->data = $from->data;
	}


	/**
	 * If description is set - show it, otherwise show name
	 *
	 * @return string
	 */
	public function showDescription()
	{
		return $this->description ? $this->description : $this->name;
	}

	public function rules()
	{
		return [
			[['name'], 'required'],
			[['name', 'ruleName', 'data'], 'string', 'max'=>255],
			['description', 'string'],
		];
	}

	public function attributeLabels()
	{
		return [
			'name'=>'Название',
			'description'=>'Описание',
		];
	}

	/**
	 * @param int $type
	 */
	public function setType($type)
	{
		$this->_type = $type;
	}
} 