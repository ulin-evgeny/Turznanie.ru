<?php
$is_admin = CurrentUser::get_user()->is_admin();

$authors_ids = array();
foreach ($items as $author) {
	// intval нужен, чтобы потом можно было выполнить функцию array_diff. ведь в $authors_ids_with_items - int, а тут без intval - string
	$authors_ids[] = intval($author->id);
}

$items_authors = ORM::factory('ItemAuthor')->where('author_id', 'in', $authors_ids)->find_all()->as_array();
$items_ids_by_author_id = [];
foreach ($items_authors as $item_author) {
	if (!$is_admin) {
		$item = ORM::factory('Item')->where('id', '=', $item_author->item_id)->where('status', '=', Status::STATUS_VISIBLE_VALUE)->find();
		if ($item->loaded()) {
			$items_ids_by_author_id[$item_author->author_id][] = $item->id;
		}
	} else {
		$items_ids_by_author_id[$item_author->author_id][] = $item_author->item_id;
	}
}

// Получаем авторов с книгами и без книг - чтобы потом правильно вывести их. С книгами - споилер. Без книг - просто строка.
$authors_ids_with_items = array_keys($items_ids_by_author_id);
$authors_ids = array_diff($authors_ids, $authors_ids_with_items);

if (!empty($items_ids_by_author_id)) {
	foreach ($items_ids_by_author_id as $author_id => $items_ids) {
		$author = ORM::factory('Author', $author_id);
		echo '<div class="clearfix">
					<div class="js-spoiler left">
						<a class="js-spoiler__btn black custom-elems__link custom-elems__link_type_underline-solid">' . $author->title . '</a>';

		if ($is_admin) {
			echo Search::render_in_seo_link($author->get_seo_url());
		}

		echo '<div class="js-spoiler__body">';

		foreach ($items_ids as $item_id) {
			$item = ORM::factory('Item', $item_id);
			echo '<a class="custom-elems__point-16 black custom-elems__link custom-elems__link_type_underline-solid" href="' . $item->get_url() . '">' . $item->name . '</a>';
		}

		echo '</div></div></div>';
	}
}

if (!empty($authors_ids)) {
	foreach ($authors_ids as $author_id) {
		$author = ORM::factory('Author', $author_id);
		echo '<div class="clearfix">
					<div class="left">
						<span class="black">' . $author->title . '</span>';

		if ($is_admin) {
			echo Search::render_in_seo_link($author->get_seo_url());
		}

		echo '</div></div>';
	}
}