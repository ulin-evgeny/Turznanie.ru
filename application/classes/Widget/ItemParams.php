<?php

class Widget_ItemParams {

	static public function render($item) {
		echo '<div class="item-params">' .
			Widget_ItemParams::render_favorites($item) .
			Widget_ItemParams::render_rating($item)
			. '</div>';
	}

	static public function render_favorites($item) {
		$favored = ORM::factory('ItemFavorite')->is_favored($item->id);
		$to_favorite = 'В избранное';
		$from_favorite = 'Из избранного';

		$result = '<a data-id="' . $item->id . '" data-to-favorite="' . ($to_favorite . '" data-from-favorite="' . $from_favorite) . '" class="item-params__btn item-params__add-favorite icon-heart' . ($favored ? ' active' : '') . '">' . ($favored ? $from_favorite : $to_favorite) . '</a>';

		return $result;
	}

	static public function render_rating($item) {
		$rate = CurrentUser::get_user()->get_item_rate($item);
		$result = '<span data-id="' . $item->id . '" class="item-params__rating js-rating">
									<a class="item-params__btn icon-up js-rating__up item-params__rating-btn' . ($rate == 1 ? ' active' : '') . '"></a>
									<span class="item-params__rating-value">' . ($item->get_rating()) . '</span>
									<a class="item-params__btn icon-down js-rating__down item-params__rating-btn' . ($rate == -1 ? ' active' : '') . '"></a>
								</span>';

		return $result;
	}
}
