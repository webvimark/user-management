<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 6/11/14
 * Time: 11:30 AM
 */

namespace app\webvimark\modules\UserManagement\forms;


use app\webvimark\modules\UserManagement\models\User;
use yii\base\Model;

class NewUserForm extends Model
{
	public $username;
	public $email;
	public $password;
	public $repeat_password;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			['username', 'filter', 'filter' => 'trim'],
			['username', 'required'],
			['username', 'unique', 'targetClass' => 'app\webvimark\modules\UserManagement\models\User', 'message' => 'This username has already been taken.'],
			['username', 'string', 'min' => 2, 'max' => 255],

			['email', 'filter', 'filter' => 'trim'],
			['email', 'required', 'on'=>'singUp'],
			['email', 'email'],
			['email', 'unique', 'targetClass' => 'app\webvimark\modules\UserManagement\models\User', 'message' => 'This email address has already been taken.'],

			['password', 'required'],
			['password', 'string', 'min' => 3],

			['repeat_password', 'required'],
			['repeat_password', 'compare', 'compareAttribute'=>'password'],
		];
	}

	/**
	 * Signs user up.
	 *
	 * @param string|null $roleName
	 *
	 * @return User|null the saved model or null if saving fails
	 */
	public function create($roleName = null)
	{
		if ($this->validate())
		{
			$user = new User();
			$user->username = $this->username;
			$user->email = $this->email;
			$user->setPassword($this->password);
//			$user->generateAuthKey();
			$user->save(false);
			return $user;
		}

		return null;
	}
} 