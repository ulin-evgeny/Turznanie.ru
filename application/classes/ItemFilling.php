<?php

class ItemFilling {

	static public function render_material_selection($material, $item_section) { ?>
		<label for="catalog_id" class="custom-elems__label page-item-filling__label">Раздел:</label>
		<select name="catalog_id" id="catalog_id" class="custom-elems__input material-section__select">
			<option value="0" <?= !$item_section ? 'selected' : '' ?>>Не выбран</option>
			<?php
			$items = ORM::factory('Catalog')->where('parent_id', '=', $material->id)->find_all()->as_array();
			foreach ($items as $item) {
				echo static::render_material_selection_option($item, $item_section);
			}
			?>
		</select>
	<?php }

	static private function render_material_selection_option($item, $item_section, $parent_string = '') {
		$children = ORM::factory('Catalog')->where('parent_id', '=', $item->id)->find_all()->as_array();
		if (count($children)) {
			// если $parent_string не пустой (уровень > 1)
			if ($parent_string) {
				$parent_string = $parent_string . ' - ' . $item->title . ' - ';
			} // если $parent_string пустой (уровень 1)
			else {
				$parent_string = $item->title . ' - ';
			}
			foreach ($children as $child) {
				static::render_material_selection_option($child, $item_section, $parent_string);
			}
		} else {
			echo '<option value="' . $item->id . '"' . ($item_section == $item->id ? ' selected' : '') . '>' . $parent_string . ' ' . $item->title . '</option>';
		}
	}

}
