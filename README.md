User management module for Yii 2
=====

Perks
---

* User management
* RBAC (roles, permissions and stuff) with web interface
* Registration, authorization, password recovery and so on
* Visit log
* Optimised (zero DB queries during usual user workflow)
* Nice widgets like GhostMenu or GhostHtml::a where elements are visible only if user has access to route where they point


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require webvimark/module-user-management
```

or add

```
"webvimark/module-user-management": "^1"
```

to the require section of your `composer.json` file.

Configuration
---

1) In your config/web.php

```php

'components'=>[
	'user' => [
		'class' => 'webvimark\modules\UserManagement\components\UserConfig',

		// Comment this if you don't want to record user logins
		'on afterLogin' => function($event) {
				\webvimark\modules\UserManagement\models\UserVisitLog::newVisitor($event->identity->id);
			}
	],
],

'modules'=>[
	'user-management' => [
		'class' => 'webvimark\modules\UserManagement\UserManagementModule',

		// 'enableRegistration' => true,

		// Add regexp validation to passwords. Default pattern does not restrict user and can enter any set of characters.
		// The example below allows user to enter :
		// any set of characters
		// (?=\S{8,}): of at least length 8
		// (?=\S*[a-z]): containing at least one lowercase letter
		// (?=\S*[A-Z]): and at least one uppercase letter
		// (?=\S*[\d]): and at least one number
		// $: anchored to the end of the string

		//'passwordRegexp' => '^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$',
		

		// Here you can set your handler to change layout for any controller or action
		// Tip: you can use this event in any module
		'on beforeAction'=>function(yii\base\ActionEvent $event) {
				if ( $event->action->uniqueId == 'user-management/auth/login' )
				{
					$event->action->controller->layout = 'loginLayout.php';
				};
			},
	],
],

```

To learn about events check:

* http://www.yiiframework.com/doc-2.0/guide-concept-events.html
* http://www.yiiframework.com/doc-2.0/guide-concept-configurations.html#configuration-format

Layout handler example in *AuthHelper::layoutHandler()*

To see full list of options check *UserManagementModule* file


2) In your config/console.php (this is needed for migrations and working with console)

```php

'modules'=>[
	'user-management' => [
		'class' => 'webvimark\modules\UserManagement\UserManagementModule',
	        'controllerNamespace'=>'vendor\webvimark\modules\UserManagement\controllers', // To prevent yii help from crashing
	],
],

```

3) Run migrations

```php

./yii migrate --migrationPath=vendor/webvimark/module-user-management/migrations/

```

4) In you base controller

```php

public function behaviors()
{
	return [
		'ghost-access'=> [
			'class' => 'webvimark\modules\UserManagement\components\GhostAccessControl',
		],
	];
}

```

Where you can go
-----

```php

<?php
use webvimark\modules\UserManagement\components\GhostMenu;
use webvimark\modules\UserManagement\UserManagementModule;

echo GhostMenu::widget([
	'encodeLabels'=>false,
	'activateParents'=>true,
	'items' => [
		[
			'label' => 'Backend routes',
			'items'=>UserManagementModule::menuItems()
		],
		[
			'label' => 'Frontend routes',
			'items'=>[
				['label'=>'Login', 'url'=>['/user-management/auth/login']],
				['label'=>'Logout', 'url'=>['/user-management/auth/logout']],
				['label'=>'Registration', 'url'=>['/user-management/auth/registration']],
				['label'=>'Change own password', 'url'=>['/user-management/auth/change-own-password']],
				['label'=>'Password recovery', 'url'=>['/user-management/auth/password-recovery']],
				['label'=>'E-mail confirmation', 'url'=>['/user-management/auth/confirm-email']],
			],
		],
	],
]);
?>

```

First steps
---

From the menu above at first you'll se only 2 element: "Login" and "Logout" because you have no permission to visit other urls
and to render menu we using **GhostMenu::widget()**. It's render only element that active user can visit.

Also same functionality has **GhostNav::widget()** and **GhostHtml:a()**

1) Login as superadmin/superadmin

2) Go to "Permissions" and play there

3) Go to "Roles" and play there

4) Go to "User" and play there

5) Relax


Usage
---

You controllers may have two properties that will make whole controller or selected action accessible to everyone

```php
public $freeAccess = true;

```

Or

```php
public $freeAccessActions = ['first-action', 'another-action'];

```

Here are list of the useful helpers. For detailed explanation look in the corresponding functions.

```php

User::hasRole($roles, $superAdminAllowed = true)
User::hasPermission($permission, $superAdminAllowed = true)
User::canRoute($route, $superAdminAllowed = true)

User::assignRole($userId, $roleName)
User::revokeRole($userId, $roleName)

User::getCurrentUser($fromSingleton = true)

```

Role, Permission and Route all have following methods

```php

Role::create($name, $description = null, $groupCode = null, $ruleName = null, $data = null)
Role::addChildren($parentName, $childrenNames, $throwException = false)
Role::removeChildren($parentName, $childrenNames)

```


Events
------

Events can be handled via config file like following

```php

'modules'=>[
	'user-management' => [
		'class' => 'webvimark\modules\UserManagement\UserManagementModule',
		'on afterRegistration' => function(UserAuthEvent $event) {
			// Here you can do your own stuff like assign roles, send emails and so on
		},
	],
],

```

List of supported events can be found in *UserAuthEvent* class

FAQ
---

**Question**: Do you have API docs?

**Answer**: Check this one http://opensource.id5.com.br/webvimark/doc/index.html (Credits to [lukBarros](https://github.com/lukBarros))

**Question**: I want users to register and login with they e-mails! Mmmmm... And they should confirm it too!

**Answer**: See configuration properties *$useEmailAsLogin* and *$emailConfirmationRequired*

**Question**: I want to have profile for user with avatar, birthday and stuff. What should I do ?

**Answer**: Profiles are to project-specific, so you'll have to implement them yourself (but you can find example here - https://github.com/webvimark/user-management/wiki/Profile-and-custom-registration). Here is how to do it without modifying this module

1) Create table and model for profile, that have user_id (connect with "user" table)

2) Check AuthController::actionRegistration() how it works (*you can skip this part*)

3) Define your layout for registration. Check example in *AuthHelper::layoutHandler()*. Now use theming to change registraion.php file

4) Define your own UserManagementModule::$registrationFormClass. In this class you can do whatever you want like validating custom forms and saving profiles

5) Create your controller where user can view profiles
