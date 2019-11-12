<?php

class Controller_CabinetCommon extends Controller {

	public $template_extra = 'cabinet';
	public $person; # Если контроллер User, то это просматриваемый пользователь. Если Cabinet, то авторизованный.
	public $cabinet_type;
	public $sent_array;

	public function before() {
		parent::before();
		$route_uri = Request::initial()->route()->uri();

		$alias_cabinet_user = Model_Seo::get_alias_by_id(Model_Seo::ID_USER, false);
		$alias_cabinet_personal = Model_Seo::get_alias_by_id(Model_Seo::ID_CABINET, false);

		$except_pages = array(Model_Seo::ID_CABINET_CHANGE_EMAIL);

		switch ($route_uri) {
			case $alias_cabinet_user:
				$this->person = ORM::factory('User')->where('username', '=', $this->request->param('part1'))->find();
				if (!$this->person->loaded()) {
					return $this->custom_redirect('/');
				}
				$this->cabinet_type = Model_Seo::ID_USER;
				if (!$this->user->is_admin()) {
					$except_pages = array_merge($except_pages, array(Model_Seo::ID_USER_MATERIALS_HIDDEN, Model_Seo::ID_USER_MATERIALS_ACTIVE));
				}
				$menu_items = Model_Seo::build_menu($this->cabinet_type, 1, $except_pages, array('position' => 2, 'part' => $this->person->username));
				break;
			case $alias_cabinet_personal:
				$except_pages = array_merge($except_pages, array(Model_Seo::ID_CABINET_CHANGE_PASSWORD));
				$this->cabinet_type = Model_Seo::ID_CABINET;
				$this->person = CurrentUser::get_user();
				$menu_items = Model_Seo::build_menu($this->cabinet_type, 1, $except_pages);
				if (!$this->person->loaded()) {
					$this->custom_redirect('/login');
				}
				break;
		}
		$this->sent_array = array(
			'menu_items' => $menu_items,
			'avatar' => Helper::const_to_client($this->person->get_photo_by_size(Model_User::PHOTO_SIZES['sm'])),
			'person' => $this->person,
			'cabinet_type' => $this->cabinet_type
		);
	}

	public function render($template, $data = array()) {
		$data = array_merge($data, $this->sent_array);
		parent::render($template, $data);
	}

	public function action_materials() {
		switch ($this->cabinet_type) {
			case Model_Seo::ID_USER:
				$page_materials_visible = ORM::factory('Seo', Model_Seo::ID_USER_MATERIALS_ACTIVE);
				$page_materials_hidden = ORM::factory('Seo', Model_Seo::ID_USER_MATERIALS_HIDDEN);
				break;
			case Model_Seo::ID_CABINET:
				$page_materials_visible = ORM::factory('Seo', Model_Seo::ID_CABINET_MATERIALS_ACTIVE);
				$page_materials_hidden = ORM::factory('Seo', Model_Seo::ID_CABINET_MATERIALS_HIDDEN);
				break;
		}
		$alias_active = $page_materials_visible->alias;
		$alias_hidden = $page_materials_hidden->alias;

		// ----------------------------------------------------------------
		// Установка $action.
		// $action - это что будет выводиться.
		// Если это личный кабинет, то если part не задан, выводится подсчет видимых и скрытых элементов, а вместе с ним и ссылки на них.
		// Если это личный кабинет и если part задан, то выводятся элементы, на которые ссылается part.
		// Если это просмотр пользователя и если смотрящий - админ, то все то же самое, как если бы это был личный кабинет.
		// Если это просмотр пользователя и если смотрящий - обычный пользователь, то всегда выводятся видимые элементы. При заходе на страницу с part идет редирект на страницу, где part не задан.
		// ----------------------------------------------------------------
		$is_admin = $this->user->is_admin();
		$materials_type = $this->request->param('part');
		if ($this->cabinet_type == Model_Seo::ID_CABINET || $this->cabinet_type == Model_Seo::ID_USER && $is_admin) {
			if ($materials_type && ($materials_type == $alias_active || $materials_type == $alias_hidden)) {
				if ($materials_type == $alias_active) {
					$action = Cabinet::ACTION_SHOW_VISIBLE;
				} elseif ($materials_type == $alias_hidden) {
					$action = Cabinet::ACTION_SHOW_HIDDEN;
				}
			} else {
				$action = Cabinet::ACTION_SHOW_COUNT;
			}
		} elseif ($this->cabinet_type == Model_Seo::ID_USER) {
			if ($materials_type) {
				$url = str_replace('/' . $materials_type, '', $this->request->url());
				HTTP::redirect($url);
			} else {
				$action = Cabinet::ACTION_SHOW_VISIBLE;
			}
		}

		$sent_array = array('action' => $action);

		// Получение item'ов
		$catalog = ORM::factory('Catalog', Model_Item::MATERIAL_ARTICLE);
		$children = $catalog->get_children();
		$children_ids = array();
		foreach ($children as $child) {
			$children_ids[] = $child->id;
		}
		$items = ORM::factory('Item')
			->where('user_id', '=', $this->person->id)
			->where('catalog_id', 'in', $children_ids);

		switch ($action) {
			case Cabinet::ACTION_SHOW_VISIBLE:
			case Cabinet::ACTION_SHOW_HIDDEN:
				switch ($action) {
					case Cabinet::ACTION_SHOW_VISIBLE:
						$items->where('status', '=', 1);
						break;
					case Cabinet::ACTION_SHOW_HIDDEN:
						$items->where('status', '=', 0);
						break;
				}
				$items->reset(FALSE);
				$count = $items->count_all();

				// Поскольку в кабинете нет choosing-panel, я строго указываю значения для $sort
				$sort = new Sort(array(
					'seo_id' => $this->cabinet_type,
					'sort_by_default' => Sort::SORT_BY_DATE,
					'sort_by' => Sort::SORT_BY_DATE,
					'sort_way' => Sort::SORT_WAY_DESC
				));
				$pagination = new Pagination(array(
					'count' => $count,
					'page' => isset($_GET['page']) ? $_GET['page'] : null,
					'on_page' => isset($_GET['on_page']) ? $_GET['on_page'] : null
				));

				$items = $items->order_by($sort->get_sort_by(), $sort->get_sort_way());
				$items = $items->limit($pagination->get_on_page());
				$items = $items->offset($pagination->get_offset());
				$items = $items->find_all()->as_array();

				$sent_array['pagination'] = $pagination;
				$sent_array['items'] = $items;
				break;
			case Cabinet::ACTION_SHOW_COUNT:
				$items = $items->find_all()->as_array();
				$items_visible = [];
				$items_hidden = [];
				foreach ($items as $item) {
					if ($item->status == 1) {
						$items_visible[] = $item;
					} else {
						$items_hidden[] = $item;
					}
				}
				$sent_array['count_visible'] = count($items_visible);
				$sent_array['count_hidden'] = count($items_hidden);
				$sent_array['page_visible'] = $page_materials_visible;
				$sent_array['page_hidden'] = $page_materials_hidden;
				break;
		}

		return $this->render('cabinet/materials', $sent_array);
	}

}
