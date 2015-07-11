<?php

use webvimark\modules\UserManagement\models\rbacDB\AuthItemGroup;
use webvimark\modules\UserManagement\models\rbacDB\Permission;
use webvimark\modules\UserManagement\models\rbacDB\Role;
use webvimark\modules\UserManagement\models\rbacDB\Route;
use yii\db\Migration;

class m141207_001649_create_basic_user_permissions extends Migration
{
	public function safeUp()
	{
		Route::refreshRoutes();

		Role::create('Admin');

		// ================= User management permissions =================
		$group = new AuthItemGroup();
		$group->name = 'User management';
		$group->code = 'userManagement';
		$group->save(false);


		Role::assignRoutesViaPermission('Admin','viewUsers', [
			'/user-management/user/index',
			'/user-management/user/view',
			'/user-management/user/grid-page-size',
		], 'View users', $group->code);

		Role::assignRoutesViaPermission('Admin','createUsers', ['/user-management/user/create'], 'Create users', $group->code);

		Role::assignRoutesViaPermission('Admin','editUsers', [
			'/user-management/user/update',
			'/user-management/user/bulk-activate',
			'/user-management/user/bulk-deactivate',
		], 'Edit users', $group->code);

		Role::assignRoutesViaPermission('Admin','deleteUsers', [
			'/user-management/user/delete',
			'/user-management/user/bulk-delete',
		], 'Delete users', $group->code);

		Role::assignRoutesViaPermission('Admin','changeUserPassword', ['/user-management/user/change-password'], 'Change user password', $group->code);

		Role::assignRoutesViaPermission('Admin','assignRolesToUsers', [
			'/user-management/user-permission/set',
			'/user-management/user-permission/set-roles',
		], 'Assign roles to users', $group->code);


		Permission::assignRoutes('viewVisitLog', [
			'/user-management/user-visit-log/index',
			'/user-management/user-visit-log/grid-page-size',
			'/user-management/user-visit-log/view',
		], 'View visit log', $group->code);


		Permission::create('viewUserRoles', 'View user roles', $group->code);
		Permission::create('viewRegistrationIp', 'View registration IP', $group->code);
		Permission::create('viewUserEmail', 'View user email', $group->code);
		Permission::create('editUserEmail', 'Edit user email', $group->code);
		Permission::create('bindUserToIp', 'Bind user to IP', $group->code);


		Permission::addChildren('assignRolesToUsers', ['viewUsers', 'viewUserRoles']);
		Permission::addChildren('changeUserPassword', ['viewUsers']);
		Permission::addChildren('deleteUsers', ['viewUsers']);
		Permission::addChildren('createUsers', ['viewUsers']);
		Permission::addChildren('editUsers', ['viewUsers']);
		Permission::addChildren('editUserEmail', ['viewUserEmail']);


		// ================= User common permissions =================
		$group = new AuthItemGroup();
		$group->name = 'User common permission';
		$group->code = 'userCommonPermissions';
		$group->save(false);

		Role::assignRoutesViaPermission('Admin','changeOwnPassword', ['/user-management/auth/change-own-password'], 'Change own password', $group->code);
	}

	public function safeDown()
	{
		Permission::deleteAll(['name'=>[
			'viewUsers',
			'createUsers',
			'editUsers',
			'deleteUsers',
			'changeUserPassword',
			'assignRolesToUsers',
			'viewVisitLog',
			'viewUserRoles',
			'viewRegistrationIp',
			'viewUserEmail',
			'editUserEmail',
			'bindUserToIp',
		]]);

		Permission::deleteAll(['name'=>[
			'changeOwnPassword',
		]]);

		Role::deleteIfExists(['name'=>'Admin']);

		AuthItemGroup::deleteAll([
			'code'=>[
				'userManagement',
				'userCommonPermissions',
			],
		]);
	}
}
