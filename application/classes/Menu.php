<?php

class Menu {

	const KEY_TITLE = 'title';
	const KEY_CHILDREN = 'children';
	const KEY_ID = 'id';
	const KEY_URL = 'url';
	const KEY_PARENT_ID = 'parent_id';
	const KEY_PRIORITY = 'priority';
	const KEY_LEVEL = 'level';

	const KEY_URL_PART_VAL = 'part_value';
	const KEY_URL_PART_POS = 'position';

	static public function render_menu($menu_items, $header, $additional_params = array()) {
		$result = '<div class="aside-menu">
						<div class="h1 aside-menu__header">' . $header . '</div>
						<menu class="aside-menu__items-wrap">';
		foreach ($menu_items as $item) {
			$result .= static::render_menu_item($item, 1, $additional_params);
		}
		$result .= '	</menu>
					</div>';
		return $result;
	}

	static private function render_menu_item($menu_item, $level, $additional_params) {
		$menu_item_url = $menu_item[static::KEY_URL];
		$menu_item_link = Helper::add_params_to_url($menu_item_url, $additional_params);
		$result = '<a class="aside-menu__link' . (Request::current()->url() == $menu_item_url ? ' active' : '') . '" href="' . $menu_item_link . '"><span class="aside-menu__link-text">' . $menu_item[static::KEY_TITLE] . '</span></a>';
		if (isset($menu_item[static::KEY_CHILDREN])) {
			$result .= '<div class="aside-menu__children-items aside-menu__children-items_level_' . $level . '">';
			foreach ($menu_item[static::KEY_CHILDREN] as $item) {
				$result .= static::render_menu_item($item, $level + 1, $additional_params);
			}
			$result .= '</div>';
		}
		return $result;
	}

}
