<?php

class ColorTags {

	public static function render_bar($items_tags, $have_link = true) {
		$result = '';
		if (count($items_tags)) {
			$result .= '
			<div class="color-tags">
				<div class="color-tags__tags-list">';
			foreach ($items_tags as $tag) {
				$result .= static::render_tag($tag, $have_link);
			}
			$result .= '
				</div>
			</div>';
		}
		return $result;
	}

	static public function render_tag($tag, $have_link = false) {
		if ($have_link) {
			$href = URL::query(array('tag' => $tag->title));
		} else {
			$href = $tag->get_link_to_search();
		}
		return '<a href="' . $href . '" class="js-tag color-tags__tag tag label ' . $tag->get_status_label() . '">' . $tag->title . '</a>';
	}

	public static function render_input($url, $tags = false) {
		if (is_array($tags)) {
			$i = 0;
			foreach ($tags as $tag) {
				$tags_arr[$i]['status'] = $tag->status;
				$tags_arr[$i]['name'] = $tag->title;
				$i++;
			}
			$tags_str = json_encode($tags_arr);
		}
		?>
		<div class="js-input-tags-wrap">
			<input name="tags" id="tags-with-colors" class="js-input-tags js-color-tags" data-url="/<?= $url ?>/"<?= isset($tags_str) ? 'data-tags=\'' . $tags_str . '\'' : '' ?>>
		</div>
	<?php }

	static public function get_most_used_tags() {
		$items_tags = DB::query(Database::SELECT,
			'SELECT tag_id, COUNT(item_id) AS `amount`
			 FROM  items_tags
			 JOIN tags ON tags.id = tag_id
			 WHERE tags.status = 1
			 GROUP BY tag_id
			 LIMIT 20'
		)
			->execute()
			->as_array();

		foreach ($items_tags as $key => $it) {
			$tag_value = ORM::factory('Tag', $it['tag_id'])->title;
			$items_tags[$key]['text'] = $tag_value;
			$items_tags[$key]['weight'] = $items_tags[$key]['amount'];
			$items_tags[$key]['link'] = ORM::factory('Tag', $it['tag_id'])->get_link_to_search();
		}
		$items_tags = json_encode($items_tags, JSON_UNESCAPED_UNICODE);
		$items_tags = urldecode($items_tags);
		return $items_tags;
	}

}