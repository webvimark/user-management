<?php

namespace webvimark\modules\UserManagement\components;

use webvimark\modules\UserManagement\models\User;
use yii\grid\ActionColumn;

class GhostActionColumn extends ActionColumn
{

    /**
     *
     * {@inheritdoc}
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        return preg_replace_callback('/\\{([\w\-\/]+)\\}/', function ($matches) use ($model, $key, $index) {
            $name = $matches[1];

            if (isset($this->visibleButtons[$name])) {
                $isVisible = $this->visibleButtons[$name] instanceof \Closure
                    ? call_user_func($this->visibleButtons[$name], $model, $key, $index)
                    : $this->visibleButtons[$name];
            } else {
                $isVisible = true;
            }

            $url = $this->createUrl($name, $model, $key, $index);

            $isVisible = $isVisible && User::canRoute($url);

            if ($isVisible && isset($this->buttons[$name])) {
                return call_user_func($this->buttons[$name], $url, $model, $key);
            }

            return '';
        }, $this->template);
    }

}