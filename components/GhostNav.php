<?php
namespace webvimark\modules\UserManagement\components;

use webvimark\modules\UserManagement\models\User;
use yii\bootstrap\Nav;

/**
 * Class GhostNav
 *
 * Show only those items in navigation menu which user can see
 * If item has no "visible" key, than "visible"=>User::canRoute($item['url') will be added
 *
 * @package webvimark\modules\UserManagement\components
 */
class GhostNav extends Nav
{
	public function init()
	{
		parent::init();

		$this->ensureVisibility($this->items);
	}

	/**
	 * @param array $items
	 *
	 * @return bool
	 */
	protected function ensureVisibility(&$items)
	{
		$allVisible = false;

		foreach ($items as &$item)
		{
			if ( isset( $item['url'] ) AND !isset( $item['visible'] ) AND !in_array($item['url'], ['', '#']))
			{
				$item['visible'] = User::canRoute($item['url']);
			}

			if ( isset( $item['items'] ) )
			{
				// If not children are visible - make invisible this node
				if ( !$this->ensureVisibility($item['items']) AND !isset( $item['visible'] ) )
				{
					$item['visible'] = false;
				}
			}

			if ( isset( $item['label'] ) AND ( !isset( $item['visible'] ) OR $item['visible'] === true ) )
			{
				$allVisible = true;
			}
		}

		return $allVisible;
	}
} 
