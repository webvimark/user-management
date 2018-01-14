<?php

namespace wpler\modules\UserManagement\controllers;

use Yii;
use wpler\modules\UserManagement\models\UserVisitLog;
use wpler\modules\UserManagement\models\search\UserVisitLogSearch;
use wpler\components\AdminDefaultController;

/**
 * UserVisitLogController implements the CRUD actions for UserVisitLog model.
 */
class UserVisitLogController extends AdminDefaultController
{
	/**
	 * @var UserVisitLog
	 */
	public $modelClass = 'wpler\modules\UserManagement\models\UserVisitLog';

	/**
	 * @var UserVisitLogSearch
	 */
	public $modelSearchClass = 'wpler\modules\UserManagement\models\search\UserVisitLogSearch';

	public $enableOnlyActions = ['index', 'view', 'grid-page-size'];
}
