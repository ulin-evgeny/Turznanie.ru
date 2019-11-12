<?php

$insert_part = array();
if ($cabinet_type == Model_Seo::ID_USER) {
	$insert_part = array('position' => 2, 'part' => $person->username);
}

switch ($action) {
	case Cabinet::ACTION_SHOW_COUNT:
		echo '<div><a class="black custom-elems__link custom-elems__link_type_underline-solid" href="' . $page_visible->get_url($insert_part) . '">' . $page_visible->get_title_menu() . ': ' . $count_visible . '</a></div>
					<div><a class="black custom-elems__link custom-elems__link_type_underline-solid" href="' . $page_hidden->get_url($insert_part) . '">' . $page_hidden->get_title_menu() . ': ' . $count_hidden . '</a></div>';
		break;
	case ($action == Cabinet::ACTION_SHOW_VISIBLE || $action == Cabinet::ACTION_SHOW_HIDDEN):
		if (!empty($items)) {
			foreach ($items as $item) {
				echo $item->render_item_in_line();
			}
			echo $pagination->render(2);
			break;
		}
}