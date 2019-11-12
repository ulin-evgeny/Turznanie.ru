<?php

class Navigation {

	static public function render_mobile_menu_source() {
		$items = ORM::factory('Catalog')->where('parent_id', '=', 0)->find_all()->as_array();
		$catalog_id = Model_Catalog::get_catalog_id_by_url(Request::initial()->url());
		if (CurrentUser::get_user()->is_admin()) {
			$admin_items = ORM::factory('Seo')->where('parent_id', '=', Model_Seo::ID_ADMIN_PANEL)->find_all()->as_array();
		}

		$result = '<div class="hidden mobile-menu-source" data-catalog_id_page="' . $catalog_id . '"><div data-catalog_id="0" class="mobile-menu-source__container" data-catalog_title="Главная страница">';
		foreach ($items as $item) {
			$result .= static::render_mobile_menu_source_item($item);
		}
		if (CurrentUser::get_user()->is_admin()) {
			foreach ($admin_items as $item) {
				$result .= static::render_mobile_menu_source_admin_item($item);
			}
		}
		$result .= '</div></div>';
		return $result;
	}

	static public function render_mobile_menu_source_admin_item($item) {
		$result = '<div class="mobile-menu-source__item" data-href="' . $item->get_url() . '">
								<div class="mobile-menu-source__item-title">' . $item->title_menu . '</div></div>';
		return $result;
	}

	static public function render_mobile_menu_source_item($item) {
		$with_children = false;
		$children = ORM::factory('Catalog')->where('parent_id', '=', $item->id)->find_all()->as_array();
		if (!empty($children)) {
			$with_children = true;
		}
		$result = '<div class="mobile-menu-source__item' . ($with_children ? ' mobile-menu-source__item_with-children' : '') . '" data-href="' . $item->get_url() . '">
								<div class="mobile-menu-source__item-title">' . $item->title . '</div>';
		if (!empty($children)) {
			$result .= '<div class="mobile-menu-source__container" data-catalog_title="' . $item->title . '" data-catalog_id="' . $item->id . '">';
			foreach ($children as $child) {
				$result .= static::render_mobile_menu_source_item($child);
			}
			$result .= '</div>';
		}
		$result .= '</div>';

		return $result;
	}

}