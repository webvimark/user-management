<?php

namespace webvimark\modules\UserManagement\controllers;

use Yii;
use webvimark\modules\UserManagement\models\UserVisitLog;
use webvimark\modules\UserManagement\models\search\UserVisitLogSearch;
use webvimark\components\AdminDefaultController;

/**
 * UserVisitLogController implements the CRUD actions for UserVisitLog model.
 */
class UserVisitLogController extends AdminDefaultController
{
	/**
	 * @var UserVisitLog
	 */
	public $modelClass = 'webvimark\modules\UserManagement\models\UserVisitLog';

	/**
	 * @var UserVisitLogSearch
	 */
	public $modelSearchClass = 'webvimark\modules\UserManagement\models\search\UserVisitLogSearch';

	protected $baseActions = ['index', 'view', 'gridPageSize'];

	/**
	 * Set layout from config
	 *
	 * @inheritdoc
	 */
	public function beforeAction($action)
	{
		if ( parent::beforeAction($action) )
		{
			$layouts = @$this->module->layouts[$this->id];

			if ( isset($layouts[$action->id]) )
			{
				$this->layout = $layouts[$action->id];
			}
			elseif ( isset($layouts['*']) )
			{
				$this->layout = $layouts['*'];
			}

			return true;
		}

		return false;
	}
}
