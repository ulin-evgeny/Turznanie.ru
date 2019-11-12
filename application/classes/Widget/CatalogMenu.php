<?php

class Widget_CatalogMenu {

	static private function render_menu_item($catalog_id, $last_part) {
		$catalog = ORM::factory('Catalog', $catalog_id);
		$children = ORM::factory('Catalog')->where('parent_id', '=', $catalog_id)->find_all()->as_array();
		$submenu_is_open = false;
		if ($last_part == $catalog->alias && count($children)) {
			$submenu_is_open = true;
		} elseif (count($children)) {
			foreach ($children as $child) {
				if ($last_part == $child->alias) {
					$submenu_is_open = true;
					break;
				}
			}
		}
		if ($submenu_is_open) {
			echo '<div class="ls-btn__outer">';
		}
		?>
		<a href="<?= $catalog->get_url() ?>" class="ls-btn<?= $last_part == $catalog->alias ? ' active' : '' ?><?= count($children) ? ' ls-btn_with-childs' : '' ?> ls-btn_standart">
			<span><?= $catalog->title ?></span>
		</a>
		<?php if ($submenu_is_open) { ?>
			<div class="ls-btn__submenu">
				<?php foreach ($children as $child) { ?>
					<a href="<?= $child->get_url() ?>" class="ls-btn<?= $last_part == $child->alias ? ' active' : '' ?> ls-btn_standart"><?= $child->title ?></a>
				<?php } ?>
			</div>
		<?php }
		if ($submenu_is_open) {
			echo '</div>';
		}
	}

	static public function render_menu($catalog_id, $last_part) {
		$items = ORM::factory('Catalog')->where('parent_id', '=', $catalog_id)->find_all()->as_array();
		if (!count($items)) {
			return;
		}
		$groups = Helper::divide_items_by_2_groups($items);
		echo '<div class="ls-panel ls-panel_without-header">
						<div class="ls-panel__body ls-panel__body_hide-protruding' . (count(array_values($groups)[1]) ? '' : ' ls-panel__body_group-only-one') . '">';
		foreach ($groups as $key => $val) {
			echo '<div class="ls-panel__' . $key . '">';
			foreach ($val as $v) {
				static::render_menu_item($v, $last_part);
			}
			echo '</div>';
		}
		echo '</div></div>';
	}

	static public function render_menu_admin($items) {
		if (!count($items)) {
			return;
		}
		$grouped_items = [];
		$i = 0;
		foreach ($items as $item) {
			$grouped_items[$i]['title'] = $item->title_menu;
			$grouped_items[$i]['url'] = $item->get_url();
			$i++;
		}
		$groups = Helper::divide_items_by_2_groups($grouped_items);
		echo '<div class="ls-panel ls-panel_without-header">
						<div class="ls-panel__body ls-panel__body_hide-protruding' . (count(array_values($groups)[1]) ? '' : ' ls-panel__body_group-only-one') . '">';
		foreach ($groups as $key => $val) {
			echo '<div class="ls-panel__' . $key . '">';
			foreach ($val as $v) { ?>
				<a href="<?= $v['url'] ?>" class="ls-btn<?= '/' == $v['url'] ? ' active' : '' ?> ls-btn_standart">
					<span><?= $v['title'] ?></span>
				</a>
				<?php
			}
			echo '</div>';
		}
		echo '</div></div>';
	}

}
