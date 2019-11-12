<?php

class Search {

	const MIN_SEARCH_LENGTH = 3;

	static public function render_in_seo_link($url) {
		return '<a class="black custom-elems__link custom-elems__link_type_underline-solid page-search__in-admin" href="' . $url . '">Найти в админке</a>';
	}

}
