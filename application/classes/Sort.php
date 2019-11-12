<?php

class Sort {

	// Константы сортировки
	const SORT_BY_ID = 'id';
	const SORT_BY_DATE = 'date';
	const SORT_BY_VIEWS = 'views';
	const SORT_BY_STATUS = 'status';
	const SORT_BY_RATING = 'rating';

	const SORT_BY_ID_TEXT = 'Порядку в БД';
	const SORT_BY_DATE_TEXT = 'Дате';
	const SORT_BY_VIEWS_TEXT = 'Просмотрам';
	const SORT_BY_STATUS_TEXT = 'Статусу';
	const SORT_BY_RATING_TEXT = 'Рейтингу';

	// Возможные направления для сортировки
	const SORT_WAY_DESC = 'desc';
	const SORT_WAY_ASC = 'asc';

	// Переменные для работы объекта сортировки
	private $sort_by_default;
	private $sort_way_default;
	private $sort_way_another;
	private $sort_by_btns = array();
	private $sort_by;
	private $sort_way;

	public function get_sort_by_default() {
		return $this->sort_by_default;
	}

	public function get_sort_way_default() {
		return $this->sort_way_default;
	}

	public function get_sort_way_another() {
		return $this->sort_way_another;
	}

	public function get_sort_by_btns() {
		return $this->sort_by_btns;
	}

	public function get_sort_by() {
		return $this->sort_by;
	}

	public function get_sort_way() {
		return $this->sort_way;
	}

	/**
	 * Sort constructor.
	 * @param $params - массив с параметрами:
	 * seo_id - для какой страницы создается сортировка (чтобы знать, что именно сортируем, какие кнопки будут (по каким столбцам сортировать)).
	 * sort_by_default - по умолчанию при загрузке страницы без GET параметров по чему сортировать?
	 * sort_way_default - по умолчанию при загрузке страницы без GET параметров каким способом сортировать?
	 * sort_by - по чему сортировать?
	 * sort_way - каким способом сортировать?
	 */
	public function __construct($params) {
		// sort_by_btns
		if (isset($params['seo_id'])) {
			$is_admin = CurrentUser::get_user()->is_admin();
			$seo_id = $params['seo_id'];
			if (in_array($seo_id, array(
				Model_Seo::ID_CATALOG_AUTHORS,
				Model_Seo::ID_CATALOG_TAGS,
				Model_Seo::ID_CATALOG_ARTICLES,
				Model_Seo::ID_CATALOG_LITERATURE,
				Model_Seo::ID_CATALOG_NEWS,
				Model_Seo::ID_CATALOG_COMMENTS
			))) {
				if ($is_admin) {
					$this->sort_by_btns[] = array(static::SORT_BY_STATUS => static::SORT_BY_STATUS_TEXT);
				}
			}
			if (in_array($seo_id, array(
				Model_Seo::ID_CATALOG_ARTICLES,
				Model_Seo::ID_CATALOG_LITERATURE,
				Model_Seo::ID_CATALOG_NEWS
			))) {
				$this->sort_by_btns[] = array(static::SORT_BY_VIEWS => static::SORT_BY_VIEWS_TEXT);
				$this->sort_by_btns[] = array(static::SORT_BY_RATING => static::SORT_BY_RATING_TEXT);
			}
			$this->sort_by_btns[] = array(static::SORT_BY_DATE => static::SORT_BY_DATE_TEXT);
			if ($is_admin) {
				$this->sort_by_btns[] = array(static::SORT_BY_ID => static::SORT_BY_ID_TEXT);
			}
		} else {
			$this->sort_by_btns[] = array(static::SORT_BY_ID => static::SORT_BY_ID_TEXT);
		}

		foreach($this->sort_by_btns as $btn) {
			$sort_by_possible_values[] = key($btn);
		}

		if (isset($params['sort_by_default']) && in_array($params['sort_by_default'], $sort_by_possible_values)) {
			$this->sort_by_default = $params['sort_by_default'];
		} else {
			$this->sort_by_default = static::SORT_BY_ID;
		}

		// sort_way_default
		$sort_way_array = array(static::SORT_WAY_DESC, static::SORT_WAY_ASC);
		if (isset($params['sort_way_default']) && in_array($params['sort_way_default'], $sort_way_array)) {
			$this->sort_way_default = $params['sort_way_default'];
			$sort_way_another = array_diff($sort_way_array, array($this->sort_way_default));
			$this->sort_way_another = reset($sort_way_another);
		} else {
			$this->sort_way_default = static::SORT_WAY_DESC;
			$this->sort_way_another = static::SORT_WAY_ASC;
		}

		// sort_by
		if (isset($params['sort_by']) && in_array($params['sort_by'], $sort_by_possible_values)) {
			$this->sort_by = $params['sort_by'];
		} else {
			$this->sort_by = $this->sort_by_default;
		}

		// sort_way
		if (isset($params['sort_way']) && in_array($params['sort_way'], $sort_way_array)) {
			$this->sort_way = $params['sort_way'];
		} else {
			$this->sort_way = $this->sort_way_default;
		}
	}

	public function get_possible_btns() {
		$result = [];
		foreach ($this->sort_by_btns as $btn) {
			$result[] = key($btn);
		}
		return $result;
	}

}
