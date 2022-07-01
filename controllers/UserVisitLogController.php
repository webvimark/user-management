<?php

namespace itbeauftragter\modules\UserManagement\controllers;

use Yii;
use itbeauftragter\modules\UserManagement\models\UserVisitLog;
use itbeauftragter\modules\UserManagement\models\search\UserVisitLogSearch;
use itbeauftragter\components\AdminDefaultController;

/**
 * UserVisitLogController implements the CRUD actions for UserVisitLog model.
 */
class UserVisitLogController extends AdminDefaultController
{
	/**
	 * @var UserVisitLog
	 */
	public $modelClass = 'itbeauftragter\modules\UserManagement\models\UserVisitLog';

	/**
	 * @var UserVisitLogSearch
	 */
	public $modelSearchClass = 'itbeauftragter\modules\UserManagement\models\search\UserVisitLogSearch';

	public $enableOnlyActions = ['index', 'view', 'grid-page-size'];
}
