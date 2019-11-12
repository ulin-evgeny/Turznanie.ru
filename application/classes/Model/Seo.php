<?php

class Model_Seo extends ORM {

	protected $_table_name = 'seo';

	public function rules() {
		return array(
			'status' => array(
				array('in_array', array(':value', array_keys(Status::STATUSES_HV)))
			)
		);
	}

	// --------------------------------------
	// Страницы
	// --------------------------------------
	const ID_MAIN = 7;

	const ID_SEARCH = 38;
	const ID_SEARCH_AUTHORS = 41;
	const ID_SEARCH_USERS = 40;
	const ID_SEARCH_TAGS = 39;
	const ID_SEARCH_MATERIALS = 37;

	const ID_ADMIN_PANEL = 24;

	// admin catalog
	const ID_CATALOG_TAGS = 21;
	const ID_CATALOG_AUTHORS = 22;
	const ID_CATALOG_SEO = 20;
	const ID_CATALOG_USERS = 23;
	const ID_CATALOG_COMMENTS = 53;
	// normal catalog
	const ID_CATALOG_ARTICLES = 16;
	const ID_CATALOG_LITERATURE = 13;
	const ID_CATALOG_NEWS = 18;

	const ID_CABINET = 28;
	const ID_CABINET_CHANGE_PASSWORD = 33;
	const ID_CABINET_CHANGE_EMAIL = 62;
	const ID_CABINET_MATERIALS_HIDDEN = 48;
	const ID_CABINET_MATERIALS_ACTIVE = 47;
	const ID_CABINET_MAILING = 30;
	const ID_UNSUBSCRIBE = 58;

	const ID_USER = 31;
	const ID_USER_MATERIALS = 32;
	const ID_USER_MATERIALS_HIDDEN = 45;
	const ID_USER_MATERIALS_ACTIVE = 44;

	const ID_ADD_ARTICLE = 56;
	const ID_ADD_NEWS = 57;
	const ID_ADD_LITERATURE = 55;

	const ID_CONTACT_US = 27;
	const ID_ABOUT = 12;
	const ID_PARTNERSHIP = 26;
	const ID_REGISTRATION = 51;
	const ID_AGREEMENT = 63;

	const ID_APPROVE = 61;

	const ID_ITEM_LITERATURE = 14;
	const ID_ITEM_ARTICLE = 15;
	const ID_ITEM_NEWS = 17;

	// --------------------------------------
	// Параметры
	// --------------------------------------
	const PARAM_KEY_ANY_NUMBER = 1;

	const PARAMS = array(
		Model_Seo::PARAM_KEY_ANY_NUMBER => ':num'
	);

	const PARAMS_TO_ALIAS = array(
		Model_Seo::PARAMS[Model_Seo::PARAM_KEY_ANY_NUMBER] => '1'
	);
	// --------------------------------------

	static public function get_alias_by_id($id, $add_slash = true) {
		$result = ($add_slash ? '/' : '') . ORM::factory('Seo', $id)->alias;
		return $result;
	}

	static public function url_get_params($url) {
		// Находит параметры на конце url (полученного через get_url()). Параметр начинается с двоеточия.
		if ($match = preg_match('/\/:[a-zA-Z0-9_.-]+($|\/)/', $url, $matches)) {
			$match = explode('/', $matches[0])[1];
			return $match;
		} else {
			return false;
		}
	}

	public function get_descendants($levels = 0, $with_self = false) {
		return parent::get_descendants_common('Seo', 'parent_id', $levels, $with_self, 'priority');
	}

	public function get_url($insert_part = array()) {
		$page = $this;
		$alias = '';
		$url = '';
		while ($page->parent_id != 0) {
			$alias = $page->alias;
			if (in_array($alias, static::PARAMS)) {
				$alias = static::PARAMS_TO_ALIAS[$alias];
			}
			if ($alias) { // $alias может быть пустым. тогда добавится лишний слэш. и функция меню будет работать некорректно.
				$url = '/' . $alias . $url;
			}
			$page = ORM::factory('Seo', $page->parent_id);
		}
		if (!$url) {
			$url = '/';
		}

		if (!empty($insert_part)) {
			$url = Helper::insert_part_to_url($url, $insert_part['position'], $insert_part['part']);
		}

		return $url;
	}

	public function get_breadcrumbs($last_part_is_current_url = true) {
		$page = $this;
		if (!$page->loaded()) {
			throw new Exception('Невозможно получить get_breadcrumbs у незагруженной страницы (!loaded())');
		}

		$iteration = 0;
		do {
			$pages[] = $page;
			if ($page->parent_id != 0) {
				$page = ORM::factory('Seo', $page->parent_id);
				$iteration++;
			}
		} while ($page->id != static::ID_MAIN);
		$pages[] = ORM::factory('Seo', static::ID_MAIN);
		$iteration++;

		$url = '';
		foreach (array_reverse($pages) as $page) {
			if ($page->id != static::ID_MAIN) {
				$url .= '/' . $page->alias;
				if ($iteration == 1 && $last_part_is_current_url) {
					$breadcrumbs[$iteration]['url'] = Request::current()->url(); // так как у книг, например, или у статей alias - это PARAM_ANY_NUMBER
				} else {
					$breadcrumbs[$iteration]['url'] = $url;
				}
			} else {
				$breadcrumbs[$iteration]['url'] = '/';
			}
			$breadcrumbs[$iteration]['title'] = $page->title;
			$iteration--;
		}
		$breadcrumbs = array_values($breadcrumbs);

		return $breadcrumbs;
	}

	static public function get_page_by_url($url) {
		if ($url != '/') {
			$url_parts = explode('/', $url);
			$parts_amount = count($url_parts);

			if (is_numeric($url_parts[$parts_amount - 1])) { // если true (на конце есть цифры), то это старница материала. если false, то каталог
				$url_parts[$parts_amount - 1] = static::PARAMS[static::PARAM_KEY_ANY_NUMBER];
			}

			$pages = ORM::factory('Seo')->where('alias', '=', $url_parts[1])->find_all()->as_array();
			if (substr($url, 0, 1) === '/') {
				unset($url_parts[0]);
			}

			foreach ($url_parts as $url_part) {
				foreach ($pages as $page) {
					$new_pages = ORM::factory('Seo')
						->where('parent_id', '=', $page->id)
						->where('alias', '=', $url_part)
						->find_all()
						->as_array();
				}
				if (count($new_pages) > 0) {
					$pages = $new_pages;
				}
			}
		} else {
			// Если $url_parts пустой (главная страница), то в $pages будут все страницы, у которых alias пустой. А нам это не нужно.
			$pages = array(ORM::factory('Seo', static::ID_MAIN));
		}

		$exception_many_pages = new Exception('Найдено больше одной страницы с построенным url ("' . $url . '") и одинаковым alias.');
		if (count($pages) > 1) {
			throw $exception_many_pages;
		} elseif (count($pages) == 1) {
			// На случаи типа /cabinet. Открывается страница информации. У нее ведь alias = '';
			$children = ORM::factory('Seo')->where('parent_id', '=', $pages[0]->id)->where('alias', '=', '')->find_all()->as_array();
			$count = count($children);
			if ($count > 1) {
				throw $exception_many_pages;
			} elseif ($count == 1) {
				return $children[0];
			} else {
				return $pages[0];
			}
		} elseif (count($pages) == 0) {
			// это нужно, так как дальше обычно идет проверка на loaded.
			return ORM::factory('Seo');
		}
	}

	static public function get_seo_data_by_page($page) {
		$seo_data['description'] = $page->description;
		$seo_data['title'] = $page->title;
		$seo_data['title_menu'] = $page->title_menu;
		$seo_data['h1'] = $page->h1;
		$seo_data['content'] = $page->content;
		$seo_data['keywords'] = $page->keywords;
		$seo_data['alias'] = $page->alias;
		$seo_data['alias'] = $page->alias;
		$seo_data['id'] = $page->id;
		return $seo_data;
	}

	public function has_parent($has_id, $last_id = null) {
		if ($last_id === null) {
			$last_id = static::ID_MAIN;
		}
		return $this->has_parent_common('Seo', 'parent_id', $has_id, $last_id);
	}

	public function get_nesting_level($higher_level_id, $last_id = null) {
		if ($last_id === null) {
			$last_id = static::ID_MAIN;
		}
		return $this->get_nesting_level_common('Seo', 'parent_id', $higher_level_id, $last_id);
	}

	public function get_title_menu() {
		return $this->title_menu ? $this->title_menu : $this->title;
	}

	static public function build_menu($start_page_id, $levels = 1, $except_pages_ids = array(), $insert_part = array()) {
		$page = ORM::factory('Seo', $start_page_id);
		$descendants = $page->get_descendants($levels);
		foreach ($descendants as $d) {
			if (!in_array($d->id, $except_pages_ids)) {
				$d_ids[] = $d->id;
			}
		}

		// Поднимаемся по предкам текущей страницы до того момента, пока предок не будет в меню. И добавляем детей предка. Это нужно, например, чтобы при выводите "Материалы -> активные" выводилась кнопка не только "активные", но и "скрытые" - тоже (дети предков).
		$tmp_page = $current_page = static::get_page_by_url(Request::initial()->url());
		if (!in_array($tmp_page->id, $except_pages_ids)) {
			if (!in_array($tmp_page->id, $d_ids) && $tmp_page->has_parent($start_page_id)) {
				while (!in_array($tmp_page->id, $d_ids)) {
					$children_ids = ORM::factory('Seo')->where('parent_id', '=', $tmp_page->id)->find_all()->as_array(null, 'id');
					$d_ids = array_merge($d_ids, array_diff($children_ids, $except_pages_ids));
					$tmp_page = ORM::factory('Seo', $tmp_page->parent_id);
				}
			}
			// if на такие случаи, как /cabinet. Это ведь вывод информации. зачем нам повторно добавлять детей?
			if ($current_page->id != $start_page_id) {
				$child = ORM::factory('Seo')->where('parent_id', '=', $tmp_page->id)->find();
				if ($child->loaded()) {
					$level = $child->get_nesting_level($start_page_id);
					if ($level > $levels) {
						$children_ids = ORM::factory('Seo')->where('parent_id', '=', $tmp_page->id)->find_all()->as_array(null, 'id');
						$d_ids = array_merge($d_ids, array_diff($children_ids, $except_pages_ids));
					}
				}
			}
		}

		// -----------------------------------------------------
		// Строим дерево для удобства вывода
		// -----------------------------------------------------
		// Берем нужные поля объекта и заносим их в массив
		$i = 0;
		foreach ($d_ids as $d) {
			$page = ORM::factory('Seo', $d);
			$menu_items[$i][Menu::KEY_ID] = $page->id;
			$menu_items[$i][Menu::KEY_TITLE] = $page->get_title_menu();
			$menu_items[$i][Menu::KEY_URL] = $page->get_url($insert_part);
			$menu_items[$i][Menu::KEY_PARENT_ID] = $page->parent_id;
			$menu_items[$i][Menu::KEY_PRIORITY] = $page->priority;
			$menu_items[$i][Menu::KEY_LEVEL] = $page->get_nesting_level($start_page_id);
			$i++;
		}

		// Находим и добавляем детей родителям (с самого последнего уровня. потому что иначе - сложнее), удаляя их из массива со всеми элементами ($menu_items)
		$tmp_level = max(array_column($menu_items, 'level'));
		while ($tmp_level > 1) {
			$tmp_level_1 = $tmp_level;
			$tmp_items_1 = array_filter($menu_items, function ($item) use ($tmp_level_1) {
				return $item[Menu::KEY_LEVEL] == $tmp_level_1;
			});
			$tmp_level_2 = $tmp_level - 1;
			$tmp_items_2 = array_filter($menu_items, function ($item) use ($tmp_level_2) {
				return $item[Menu::KEY_LEVEL] == $tmp_level_2;
			});
			foreach ($tmp_items_2 as $tmp_key_2 => $tmp_val_2) {
				$id = $tmp_val_2[Menu::KEY_ID];
				$children = array_filter($tmp_items_1, function ($tmp_item_1) use ($id) {
					return $tmp_item_1[Menu::KEY_PARENT_ID] == $id;
				});
				if (!empty($children)) {
					$menu_items[$tmp_key_2][Menu::KEY_CHILDREN] = $children;
					foreach ($children as $child_key => $child_val) {
						unset($menu_items[$child_key]);
					}
				}
			}
			$tmp_level--;
		}

		return $menu_items;
	}
	// ---------------------------------------------------------------------
}
