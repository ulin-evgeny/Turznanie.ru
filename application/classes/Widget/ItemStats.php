<?php

class Widget_ItemStats {

	/**
	 * @param $item
	 * @param $details
	 * Массив строк.
	 * Возможные элементы:
	 *
	 * all (Отрендерить все, что есть. По умолчанию), comments, rating, date, views
	 *
	 * Порядок элементов не различается.
	 * @return string
	 */
	static public function render($item, $details = array('all')) {
		$user = CurrentUser::get_user();
		$comments_visible = count(ORM::factory('ItemComment')->where('item_id', '=', $item->id)->where('status', '=', Status::STATUS_VISIBLE_VALUE)->find_all()->as_array());
		$comments_hidden = count(ORM::factory('ItemComment')->where('item_id', '=', $item->id)->where('status', '=', Status::STATUS_HIDDEN_VALUE)->find_all()->as_array());
		$date = date("d.m.y", strtotime($item->date));

		$result = '<div class="item-stats">';
		if (in_array('comments', $details) || in_array('rating', $details) || in_array('all', $details)) {
			$result .= '<div class="item-stats__can item-stats__elem">';
			if (in_array('comments', $details) || in_array('all', $details)) {
				$result .= '<span class="item-stats__comments">
					<span class="icon-comment"></span>
					<span class="item-stats__num">';
				if ($user->is_admin()) {
					$result .= '<span class="item-stats__num-comments custom-color_status_visible">' . $comments_visible . '</span>|<span class="item-stats__num-comments custom-color_status_hidden">' . $comments_hidden . '</span>';
				} else {
					$result .= $comments_visible;
				}
				$result .= '</span></span>';
			}
			if (in_array('rating', $details) || in_array('all', $details)) {
				$result .= '<span class="item-stats__rating">
					<span>
						<span class="icon-up item-stats__num-rating-up"></span>
						<span class="item-stats__num-rating-value">' . ($item->get_rating()) . '</span>
						<span class="icon-down item-stats__num-rating-down"></span>
					</span>
				</span>';
			}
			$result .= '</div>';
		}
		if (in_array('date', $details) || in_array('views', $details) || in_array('all', $details)) {
			$result .= '<div class="item-stats__dav item-stats__elem">';
			if (in_array('date', $details) || in_array('all', $details)) {
				$result .= '
					<span class="item-stats__date">
						<span class="icon-clock-1" ></span>
						<span> ' . $date . '</span>
					</span>';
			}
			if (in_array('views', $details) || in_array('all', $details)) {
				$result .= '
					<span class="item-stats__views">
						<span class="icon-eye" ></span >
						<span > ' . $item->views . '</span >
					</span >';
			}
			$result .= '</div>';
		}

		$result .= '</div>';
		return $result;
	}
}
