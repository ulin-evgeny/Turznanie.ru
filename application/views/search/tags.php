<?php
$is_admin = CurrentUser::get_user()->is_admin();

$tags_ids = array();
foreach ($items as $tag) {
	// intval нужен, чтобы потом можно было выполнить функцию array_diff. ведь в $tags_ids_with_items - int, а тут без intval - string
	$tags_ids[] = intval($tag->id);
}

$items_tags = ORM::factory('ItemTag')->where('tag_id', 'in', $tags_ids)->find_all()->as_array();
$items_ids_by_tag_id = array();
foreach ($items_tags as $it) {
	$items_ids_by_tag_id[$it->tag_id][] = $it->item_id;
}

$tags_ids_with_items = array_keys($items_ids_by_tag_id);
$tags_ids = array_diff($tags_ids, $tags_ids_with_items);

if (!empty($items_ids_by_tag_id)) {
	$items_ids_by_tags_and_material_types = array();
	foreach ($items_ids_by_tag_id as $tag_id => $item_ids) {
		$i = 0;
		foreach ($item_ids as $item_id) {
			$item = ORM::factory('Item')->where('id', '=', $item_id);
			if (!$is_admin) {
				$item = $item->where('status', '=', Status::STATUS_VISIBLE_VALUE);
			}
			$item->find();
			if ($item->loaded()) {
				$items_ids_by_tags_and_material_types[$tag_id][$item->get_material_catalog()->id][$i++] = $item->id;
			}
		}
	}

	foreach ($items_ids_by_tags_and_material_types as $tag_id => $material_type_ids) {
		foreach ($material_type_ids as $material_type_id => $items) {
			$count_items_ids_by_tags_and_material_types[$tag_id][$material_type_id] = count($items_ids_by_tags_and_material_types[$tag_id][$material_type_id]);
		}
	}

	foreach ($count_items_ids_by_tags_and_material_types as $tag_id => $count_items_ids_by_material_type) {
		$tag = ORM::factory('Tag', $tag_id);
		echo '<div class="clearfix">
					<div class="js-spoiler left">
						<a class="js-spoiler__btn black custom-elems__link custom-elems__link_type_underline-solid">' . $tag->title . ' (' . count($count_items_ids_by_material_type) . ')</a>';

		if ($is_admin) {
			echo Search::render_in_seo_link($tag->get_seo_url());
		}

		echo '	<div class="js-spoiler__body">';
		foreach ($count_items_ids_by_material_type as $material_id => $count_items_ids) {
			$catalog = ORM::factory('Catalog', $material_id);
			echo '<a class="custom-elems__point-16 black custom-elems__link custom-elems__link_type_underline-solid" href="/' . $catalog->alias . '?tag=' . $tag->title . '">' . $catalog->title . ' (' . $count_items_ids . ')</a>';
		}
		echo '	</div>
					</div>
				</div>';
	}
}

if (!empty($tags_ids)) {
	foreach ($tags_ids as $tag_id) {
		$tag = ORM::factory('Tag', $tag_id);
		echo '<div class="clearfix">
					<div class="left">
						<span class="black">' . $tag->title . '</span>';

		if ($is_admin) {
			echo Search::render_in_seo_link($tag->get_seo_url());
		}

		echo '	</div>
				</div>';
	}
}