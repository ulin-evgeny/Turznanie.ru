<?php

if ($count_materials) {
	echo '<div><a class="black custom-elems__link custom-elems__link_type_underline-solid" href="' . Helper::add_params_to_url(ORM::factory('Seo', Model_Seo::ID_SEARCH_MATERIALS)->get_url(), $menu_params) . '">Найдено материалов: ' . $count_materials . '</a></div>';
}
if ($count_tags) {
	echo '<div><a class="black custom-elems__link custom-elems__link_type_underline-solid" href="' . Helper::add_params_to_url(ORM::factory('Seo', Model_Seo::ID_SEARCH_TAGS)->get_url(), $menu_params) . '">Найдено тегов: ' . $count_tags . '</a></div>';
}
if ($count_users) {
	echo '<div><a class="black custom-elems__link custom-elems__link_type_underline-solid" href="' . Helper::add_params_to_url(ORM::factory('Seo', Model_Seo::ID_SEARCH_USERS)->get_url(), $menu_params) . '">Найдено пользователей: ' . $count_users . '</a></div>';
}
if ($count_authors) {
	echo '<div><a class="black custom-elems__link custom-elems__link_type_underline-solid" href="' . Helper::add_params_to_url(ORM::factory('Seo', Model_Seo::ID_SEARCH_AUTHORS)->get_url(), $menu_params) . '">Найдено авторов: ' . $count_authors . '</a></div>';
}