<?php
namespace itbeauftragter\modules\UserManagement\components;

use yii\base\Event;

class AbstractItemEvent extends Event
{
    public $parentName;
    public $childrenNames;
    public $throwException = false;
}
