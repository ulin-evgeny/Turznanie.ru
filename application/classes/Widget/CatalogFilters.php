<?php

class Widget_CatalogFilters {

	/*
	 Важный момент! data-href у фильтров render_filter_date и render_filter_pages равен единичкам, т.к. 0 не создает url. этот параметр меняется в JS при изменении ползунка
	*/

	static public function render_filter_date() {
		$result = '<div class="ls-panel catalog-filter filter-date js-spoiler active">
								<div class="ls-panel__header">
									<span class="ls-panel__title">Дата</span>
									<a class="ls-panel__toggle-btn js-spoiler__btn"></a>
								</div>
								<div class="ls-panel__body ls-panel__body_type-panel js-spoiler__body clearfix">
									<div class="clearfix ls-panel__inputs js-filter-values" data-href="' . URL::query(array('date_from' => 1, 'date_to' => 1)) . '">
										<div class="ls-panel__from ls-panel__amount-wrap">
											<label>с</label>
											<input class="filter-date__input-from" style="border:0; width:78px;"/>
										</div>
										<div style="float: right;" class="ls-panel__to ls-panel__amount-wrap">
											<label>по</label>
											<input class="filter-date__input-to" style="border:0; width:78px; text-align: right;"/>
										</div>
									</div>
									' . static::render_reset_btn(URL::query(array('date_from' => null, 'date_to' => null)), true) . '
								</div>
							</div>';

		return $result;
	}

	static public function render_filter_pages() {
		$result = '<div class="ls-panel catalog-filter filter-pages js-spoiler active">
								<div class="ls-panel__header">
									<span class="ls-panel__title">Страниц</span>
									<a class="ls-panel__toggle-btn js-spoiler__btn"></a>
								</div>
								<div class="ls-panel__body ls-panel__body_type-panel js-spoiler__body clearfix">
									<div class="js-filter-values" data-href="' . URL::query(array('pages_from' => 1, 'pages_to' => 1)) . '">
										<div class="filter-pages__slider" id="slider-range"></div>
										<div>
											<div style="margin-right: 16px;" class="ls-panel__from ls-panel__amount-wrap">
												<label class="ls-panel__filter-label">от</label>
												<input class="filter-pages__input-from page-catalog__input-auto-width" style="border:0; max-width:100%;"/>
											</div>
											<div style="float: left;" class="ls-panel__to ls-panel__amount-wrap">
												<label class="ls-panel__filter-label">до</label>
												<input class="filter-pages__input-to page-catalog__input-auto-width" style="border:0; max-width:100%;"/>
											</div>
										</div>
										' . static::render_reset_btn(URL::query(array('pages_from' => null, 'pages_to' => null))) . '
									</div>
								</div>
							</div>';

		return $result;
	}

	/* ЗАКОММЕНТИРОВАЛ, ТАК КАК ДРУГИХ ФИЛЬТРОВ БОЛЬШЕ НЕТ
	public static function render_filter($filter_values_by_type, $get_filters, $ftype) {
		$items = $filter_values_by_type[$ftype];
		if (empty($items)) {
			return;
		}
		$groups = Helper::divide_items_by_2_groups($items);
		$currfilter = $get_filters[$ftype];
		$result = '
		<div class="ls-panel js-spoiler active">
			<div class="ls-panel__header">
				<span class="ls-panel__title">' . ORM::factory('Filter', $ftype)->title . '</span>
				<a class="ls-panel__toggle-btn js-spoiler__btn"></a>
			</div>
			<div class="ls-panel__body ls-panel__body_hide-protruding js-spoiler__body clearfix js-filter' . (count(array_values($groups)[1]) ? '' : ' ls - panel__body_group - only - one') . '" rel="' . $ftype . '">';

		foreach ($groups as $key => $val) {
			$result .= '<div class="ls-panel__' . $key . '" >';
			foreach ($val as $v) {
				$tmp = $currfilter;
				if (isset($currfilter) AND in_array($v['id'], $currfilter)) {
					// удаление фильтра из массива (если он активный)
					$active = true;
					$k = array_search($v['id'], $tmp);
					unset($tmp[$k]);
				} else {
					// и добавление, если не активный
					$active = false;
					$tmp[] = $v['id'];
				}


				$url_value = ($tmp ? implode(';', $tmp) : NULL);
				$result .= '<a data-key="' . $k . '" href="' . URL::query(array('filter_' . $ftype => $url_value)) . '" class="ls-btn ls-btn_with-checkmark' . ($active ? ' active' : '') . '" rel="' . $v['id'] . '">
							<span class="ls-btn__checkmark"></span>
							<span>' . $v['title'] . '</span>
							<span class="ls-btn__count">' . $v['amount'] . '</span>
						</a>';
			}
			$result .= '</div >';
		}

		$result .= '</div></div>';

		return $result;
	}
	*/


	static public function does_render_filters($params, $material_id) {
		switch (true) {
			case ($material_id === Model_Item::MATERIAL_LITERATURE):
				return isset($params['max_pages']) && isset($params['min_pages']) && intval($params['min_pages']) !== intval($params['max_pages']);
				break;
			case ($material_id === Model_Item::MATERIAL_ARTICLE || $material_id === Model_Item::MATERIAL_NEWS):
				$date_1 = new DateTime(Helper::unix_time_to_mysql_time($params['min_date']));
				$date_2 = new DateTime(Helper::unix_time_to_mysql_time($params['max_date']));
				$day_1 = $date_1->format('d');
				$day_2 = $date_2->format('d');
				return isset($params['max_date']) && isset($params['min_date']) && intval($day_1) !== intval($day_2);
				break;
		}
	}

	static public function render_catalog_filters($params, $material_id) {
		$result = '';

		if (static::does_render_filters($params, $material_id)) {
			switch (true) {
				case ($material_id === Model_Item::MATERIAL_LITERATURE):
					$result .= static::render_filter_pages();
					break;
				case ($material_id === Model_Item::MATERIAL_ARTICLE || $material_id === Model_Item::MATERIAL_NEWS):
					$result .= static::render_filter_date();
					break;
			}
		}

		if ($result) {
			$result = '<div class="js-catalog-filters">' . $result . '</div>';
		}

		return $result;
	}

	static private function render_reset_btn($href, $left_on_lg = false) {
		$result = '<div class="clearfix">
								<a href="' . $href . '" onclick="event.preventDefault(); url_query(\'' . $href . '\');" class="ls-panel__reset-btn black' . ($left_on_lg ? ' ls-panel__reset-btn_left-on-lg' : '') . '">Сбросить</a>
							</div>';

		return $result;
	}
}
