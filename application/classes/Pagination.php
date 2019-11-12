<?php

class Pagination {

	// ----------------------------------------
	// конастанты
	// ----------------------------------------
	const DIRECTION_LEFT = 'left';
	const DIRECTION_RIGHT = 'right';
	const EXTRA_ACTIVE = 'active';

	// ----------------------------------------
	// поля
	// ----------------------------------------
	private $count;
	private $on_page_array = array(10, 20, 30, 40);
	private $on_page;

	private $total_pages;
	private $pages;
	private $page;

	// ----------------------------------------
	// методы
	// ----------------------------------------
	public function get_count() {
		return $this->count;
	}

	public function get_on_page() {
		return $this->on_page;
	}

	public function get_on_page_array() {
		return $this->on_page_array;
	}

	public function get_page() {
		return $this->page;
	}

	public function get_pages() {
		return $this->pages;
	}

	private function render_three_dots() {
		return '<span class="pagination__three-dots">...</span>';
	}

	private function render_arrow_btn($direction, $url) {
		return '<a href="' . $url . '" class="pagination__btn pagination__btn_type_arrow pagination__btn_direction_' . $direction . ' ' . $direction . '">' . ($direction == static::DIRECTION_LEFT ? '<' : '>') . '</a>';
	}

	private function render_btn($text, $url, $extra = null) {
		return '<a ' . ($extra == static::EXTRA_ACTIVE ? '' : 'href="' . $url . '"') . ' class="pagination__btn' . ($extra ? ' ' . $extra : '') . '">' . $text . '</a>';
	}

	private function get_url_by_page($page) {
		if ($page == 1) {
			$page = null;
		}
		return URL::query(array('page' => $page));
	}

	/**
	 * Pagination constructor.
	 * @param $params - массив с параметрами:
	 * count - количество элементов, для которых делается пагинация
	 * on_page_array - массив, в котором расположены возможные вариации количества элементов на странице;
	 * on_page - количество элементов на странице;
	 * page - текущая страница.
	 */
	public function __construct(array $params = array()) {
		if ($params['count']) {
			$this->count = intval($params['count']);
		} else {
			$this->count = 0;
		}

		if (isset($params['on_page_array'])) {
			$this->on_page_array = $params['on_page_array'];
		} else {
			$this->on_page_array = array(10, 20, 30, 40);
		}

		if ($params['on_page'] && in_array($params['on_page'], $this->on_page_array)) {
			$this->on_page = intval($params['on_page']);
		} else {
			$this->on_page = reset($this->on_page_array);
		}

		// Количество страниц с минимальным on_page_array
		$this->total_pages = ceil($this->count / reset($this->on_page_array));

		// Количество страниц с текущим on_page
		$this->pages = ceil($this->count / $this->on_page);

		// Кол-во кнопок (без стрелочек "вперед", "назад" и без кнопок для перехода на первую / последнюю страницу
		$this->amount_btns = 5;

		// Текущая страница
		$page = intval($params['page']);
		switch (true) {
			case ($page > $this->pages):
				$this->page = $this->pages;
				break;
			case ($page <= $this->pages && $page > 1):
				$this->page = $page;
				break;
			case($page < 1):
				$this->page = 1;
				break;
		}
	}

	/**
	 * Рендер пагинации
	 * @param $get_params - В $get_params должно быть: page, on_page, count
	 * @param $style
	 * @return string
	 */
	public function render($style) {
		// Рендерить ли вообще пагинацию?
		if ($this->total_pages < 2) {
			return;
		}

		$result = '';
		if ($this->pages > 1) {
			// ----------------------------------------------------------
			// Вычисление количества кнопок и их отрисовка (если их больше одной)
			// ----------------------------------------------------------
			$half = floor($this->amount_btns / 2);
			$to_left = $to_right = $half;

			// Проверка на минимум и максимум (чтобы to_right или to_left не выходили за пределы кнопок первой и последней страниц)
			if (($this->page + $to_right) > $this->pages) {
				$to_right_new = $this->pages - $this->page;
				$remainder_of_right = $to_right - $to_right_new;
				$to_right = $to_right_new;
				$to_left += $remainder_of_right;
			}
			if (($this->page - $to_left) < 1) {
				$to_left_new = $this->page - 1;
				$remainder_of_left = $to_left - $to_left_new;
				$to_left = $to_left_new;
				$to_right += $remainder_of_left;
			}
			if (($this->page + $to_right) > $this->pages) {
				$to_right = $this->pages - $this->page;
			}
			if (($this->page - $to_left) < 1) {
				$to_left = $this->page - 1;
			}

			// Кнопки первой и последней страниц
			$start_btn = false;
			$end_btn = false;
			if ($this->page - $to_left > 1) {
				$start_btn = true;
			}
			if ($this->page + $to_right < $this->pages) {
				$end_btn = true;
			}

			if (!$start_btn && $end_btn) {
				/*
				Допустим, всего должно быть 5 кнопок.
				Я на второй странице, $start_btn нет, $end_btn есть. зачем вычитать из $to_right? будет же 4 кнопки: 1, 2, 3 ... 40.
				Я на первой странице, $start_btn нет, $end_btn есть. $to_right = 4 + $end_btn = 5. и плюс текущая страница = 6.
				*/
				$to_right--;
			} elseif ($start_btn && !$end_btn) {
				/*
				То же самое, что и $to_right (читай выше)
				*/
				$to_left--;
			} elseif ($start_btn && $end_btn && $to_right > 0) {
				$to_left--;
				$to_right--;
			}

			$result = '<div class="pagination pagination_style_' . $style . '">
									<div class="pagination__wrap clearfix">';

			// отрисовка кнопки "Назад"
			if ($this->page > 1) {
				$result .= $this->render_arrow_btn(static::DIRECTION_LEFT, $this->get_url_by_page($this->page - 1));
			}

			// отрисовка кнопки первой страницы
			if ($start_btn) {
				$result .= $this->render_btn(1, $this->get_url_by_page(1));
				$result .= $this->render_three_dots();
			}

			// отрисовка левой части
			for ($i = $this->page - 1, $tmp = $to_left; $tmp > 0; $tmp--, $i--) {
				$page = $this->page - $tmp;
				$result .= $this->render_btn($page, $this->get_url_by_page($page));
			}

			// текущая страница
			$result .= $this->render_btn($this->page, null, static::EXTRA_ACTIVE);

			// отрисовка правой части
			for ($i = $this->page + 1, $tmp = $to_right; $tmp > 0; $tmp--, $i++) {
				$result .= $this->render_btn($i, $this->get_url_by_page($i));
			}

			// отрисовка кнопки последней страницы
			if ($end_btn) {
				$result .= $this->render_three_dots();
				$result .= $this->render_btn($this->pages, $this->get_url_by_page($this->pages));
			}

			// отрисовка кнопки "Вперед"
			if ($this->page < $this->pages) {
				$result .= $this->render_arrow_btn(static::DIRECTION_RIGHT, $this->get_url_by_page($this->page + 1));
			}
			$result .= '</div>
							</div>';
			// ----------------------------------------------------------
		}

		// В стиле 2 on_page выводится под пагинацией. В стиле 1 - где захочет разработчик, там и выведет.
		if ($style == 2) {
			$result .= $this->on_page_render();
		}

		return $result;
	}

	public function on_page_render() {
		$result = '<div class="on-page"><div class="on-page__wrap">Показывать по:';
		$on_page = $this->get_on_page();
		$on_page_array = $this->get_on_page_array();
		foreach ($on_page_array as $v) {
			$active = ($v === $on_page);
			$result .= '<a class="on-page__item' . ($active ? '' : ' black') . '" href="' . URL::query(array('page' => null, 'on_page' => $v != $on_page_array[0] ? $v : null)) . '">' . $v . '</a>';
		}
		$result .= '</div></div>';
		return $result;
	}

	public function get_offset() {
		$offset = ($this->get_page() - 1) * $this->get_on_page();
		return $offset;
	}

	public function select_items($items) {
		$offset = $this->get_offset();
		$selected_items = [];
		for ($i = $offset; $i < $this->get_on_page() * $this->get_page(); $i++) {
			if (!$items[$i]) {
				break;
			}
			$selected_items[] = $items[$i];
		}
		return $selected_items;
	}

}
