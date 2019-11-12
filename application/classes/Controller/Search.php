<?php

class Controller_Search extends Controller {

	public $search_text;
	public $sent_array = array();

	public function before() {
		parent::before();

		if (!isset($_GET['text'])) {
			return $this->go_back();
		}

		$this->search_text = $_GET['text'];
		// HTMLPurifier нужен, так как перед сохранением пользовательского ввода, он (ввод) очищается - с помощью htmlpurifier'a. и для соответствия делаем с поисковой фразой то же самое. к тому же, поиская фраза используется во view.
		$purifier = HelperHTMLPurifier::get_purifier();
		$this->search_text = $purifier->purify($this->search_text);

		if (!mb_strlen($this->search_text)) {
			return $this->go_back();
		} elseif (mb_strlen($this->search_text) < Search::MIN_SEARCH_LENGTH) {
			parent::render('pages/message', array(
				'text' => 'Поиск по "' . $this->search_text . '".<br>Нельзя произвести поиск по фразе, которая короче ' . Search::MIN_SEARCH_LENGTH . ' ' . Helper::form_of_word(Search::MIN_SEARCH_LENGTH, 'символа', 'символов', 'символов') . '.',
				'btn_text' => 'На главную',
				'btn_href' => '/'
			));
			return $this->request->action('nothing');
		}

		$this->template_extra = 'search';

		$is_admin = $this->user->is_admin();

		$menu_items = Model_Seo::build_menu(Model_Seo::ID_SEARCH, 1);

		$header = '<a class="black aside-menu__header-link" href="' . Helper::add_params_to_url(ORM::factory('Seo', Model_Seo::ID_SEARCH)->get_url(), $this->request->query()) . '">Результаты поиска</a>';

		$this->sent_array = array(
			'menu_params' => array('text' => $this->search_text),
			'header' => $header,
			'menu_items' => $menu_items,
			'is_admin' => $is_admin,
			'search_text' => $this->search_text
		);
	}

	public function action_index() {
		$search_type = $this->request->param('search_type');
		if ($search_type) {
			$alias_tags = ORM::factory('Seo', Model_Seo::ID_SEARCH_TAGS)->alias;
			$alias_materials = ORM::factory('Seo', Model_Seo::ID_SEARCH_MATERIALS)->alias;
			$alias_authors = ORM::factory('Seo', Model_Seo::ID_SEARCH_AUTHORS)->alias;
			$alias_users = ORM::factory('Seo', Model_Seo::ID_SEARCH_USERS)->alias;
			switch ($search_type) {
				case $alias_tags:
					$items = static::get_tags();
					$page = 'tags';
					$seo_page = ORM::factory('Seo', Model_Seo::ID_SEARCH_TAGS);
					break;
				case $alias_materials:
					$items = static::get_materials();
					$page = 'materials';
					$seo_page = ORM::factory('Seo', Model_Seo::ID_SEARCH_MATERIALS);
					break;
				case $alias_authors:
					$items = static::get_authors();
					$page = 'authors';
					$seo_page = ORM::factory('Seo', Model_Seo::ID_SEARCH_AUTHORS);
					break;
				case $alias_users:
					$items = static::get_users();
					$page = 'users';
					$seo_page = ORM::factory('Seo', Model_Seo::ID_SEARCH_USERS);
					break;
			}
			$count = $items->reset_and_count();
			return $this->render('search/' . $page, array(
				'items' => $items,
				'not_found' => !$count,
				'seo_page' => $seo_page
			));
		} else {
			$count_users = $this->get_users()->count_all();
			$count_tags = $this->get_tags()->count_all();
			$count_materials = $this->get_materials()->count_all();
			$count_authors = $this->get_authors()->count_all();

			return $this->render('search/search', array(
				'search_main_page' => true,
				'count_users' => $count_users,
				'count_tags' => $count_tags,
				'count_materials' => $count_materials,
				'count_authors' => $count_authors,
				'not_found' => !$count_users && !$count_tags && !$count_materials && !$count_authors
			));
		}
	}

	public function render($template, $data = array()) {
		if (!isset($data['search_main_page'])) {
			$count = $data['items']->reset_and_count();

			// Поскольку в поиске нет choosing-panel, я строго указываю значения для $sort
			$sort = new Sort(array(
				'seo_id' => $data['seo_page']->id,
				'sort_by_default' => Sort::SORT_BY_DATE,
				'sort_by' => Sort::SORT_BY_DATE,
				'sort_way' => Sort::SORT_WAY_DESC
			));
			$pagination = new Pagination(array(
				'count' => $count,
				'page' => isset($_GET['page']) ? $_GET['page'] : null,
				'on_page' => isset($_GET['on_page']) ? $_GET['on_page'] : null
			));

			$items = Catalog::selection_and_sorting_orm_items($data['items'], $sort, $pagination)->find_all()->as_array();
			$this->sent_array['items'] = $items;
			$this->sent_array['pagination'] = $pagination;
			$this->sent_array['sort'] = $sort;
		}

		$data = array_merge($data, $this->sent_array);
		parent::render($template, $data);
	}


	public function get_materials() {
		$words = explode(' ', $this->search_text);
		foreach ($words as $key => $val) {
			if (mb_strlen($val) < 3) {
				unset($words[$key]);
			}
		}

		if (count($words) > 1) {
			$items = ORM::factory('Item')->where('name', 'LIKE', '%' . reset($words) . '%');
			array_shift($words);
			foreach ($words as $word) {
				$items = $items->or_where('name', 'LIKE', '%' . $word . '%');
			}
		} else {
			$items = ORM::factory('Item')->where('name', 'LIKE', '%' . $this->search_text . '%');
		}

		return $items;
	}

	public function get_tags() {
		$tags = ORM::factory('Tag')->where('title', 'LIKE', '%' . $this->search_text . '%');
		if (!$this->sent_array['is_admin']) {
			// Для обычных пользователей выводим только те теги, с которыми есть публикации. Для админа любые теги.
			$tags_ids = $tags->find_all()->as_array(null, 'id');
			if (!empty($tags_ids)) {
				$tags_ids_with_items = ORM::factory('ItemTag')->where('tag_id', 'in', $tags_ids)->find_all()->as_array(null, 'tag_id');
				if (!empty($tags_ids_with_items)) {
					$items_tags_ids_unique = array_unique($tags_ids_with_items);
					$tags = ORM::factory('Tag')->where('id', 'in', $items_tags_ids_unique)->where('status', '=', 1);
				} else {
					$tags = ORM::factory('Tag')->where('id', '=', '-1');
				}
			} else {
				$tags = ORM::factory('Tag')->where('id', '=', '-1');
			}
		}

		return $tags;
	}

	public function get_users() {
		$users = ORM::factory('User')->where('username', 'LIKE', '%' . $this->search_text . '%');
		return $users;
	}

	public function get_authors() {
		$authors = ORM::factory('Author')->where('title', 'LIKE', '%' . $this->search_text . '%');
		if (!$this->sent_array['is_admin']) {
			// Для обычных пользователей выводим только тех авторов, с которыми есть публикации. Для админа любых авторов.
			$authors_ids = $authors->find_all()->as_array(null, 'id');
			if (!empty($authors_ids)) {
				$authors_ids_with_items = ORM::factory('ItemAuthor')->where('author_id', 'in', $authors_ids)->find_all()->as_array(null, 'author_id');
				if (!empty($authors_ids_with_items)) {
					$items_authors_ids_unique = array_unique($authors_ids_with_items);
					$authors = ORM::factory('Author')->where('id', 'in', $items_authors_ids_unique)->where('status', '=', 1);
				} else {
					$authors = ORM::factory('Author')->where('id', '=', '-1');
				}
			} else {
				$authors = ORM::factory('Author')->where('id', '=', '-1');
			}
		}

		return $authors;
	}

}