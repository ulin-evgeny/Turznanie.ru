<?php

class Controller_Admin_Catalog extends AdminController {

	public $seo_page;

	public function before() {
		parent::before();
		$this->seo_page = ORM::factory('Seo')->where('alias', '=', $this->request->param('alias'))->where('parent_id', '=', Model_Seo::ID_ADMIN_PANEL)->find();
		if (!$this->seo_page->loaded()) {
			$this->go_home();
		}
	}

	public function action_index() {
		if ($this->seo_page->id == Model_Seo::ID_CATALOG_AUTHORS || $this->seo_page->id == Model_Seo::ID_CATALOG_TAGS) {
			switch ($this->request->param('part1')) {
				case 'change_status':
					return $this->change_status();
					break;
				case 'change_title':
					return $this->change_title();
					break;
				case 'delete_item':
					return $this->delete_item();
					break;
				case 'create_item':
					return $this->create_item();
					break;
			}
		}

		if ($this->request->is_ajax()) {
			$this->template_main = static::TEMPLATE_AJAX;
		}

		// ===================================
		// Установка get_params, получение $sort, $pagination и $items
		// ===================================
		switch ($this->seo_page->id) {
			case Model_Seo::ID_CATALOG_AUTHORS:
				$item_type = 'Author';
				break;
			case Model_Seo::ID_CATALOG_TAGS:
				$item_type = 'Tag';
				break;
			case Model_Seo::ID_CATALOG_USERS:
				$item_type = 'User';
				break;
			case Model_Seo::ID_CATALOG_SEO:
				$item_type = 'Seo';
				break;
			case Model_Seo::ID_CATALOG_COMMENTS:
				$item_type = 'ItemComment';
				break;
		}

		$id = isset($_GET['id']) ? $_GET['id'] : null;
		$items = ORM::factory($item_type);
		if (!$id) {
			$query = 'SELECT count(*) AS count FROM ' . $items->table_name();
			if ($this->seo_page->id == Model_Seo::ID_CATALOG_SEO) {
				$query .= ' WHERE status = ' . Status::STATUS_VISIBLE_VALUE;
			}
			$count = DB::query(Database::SELECT, $query)
				->execute()
				->as_array(null, 'count');
			$count = reset($count);
		} else {
			$count = 1;
		}

		$sort = new Sort(array(
			'seo_id' => $this->seo_page->id,
			'sort_by_default' => Sort::SORT_BY_ID,
			'sort_way_default' => Sort::SORT_WAY_DESC,
			'sort_by' => isset($_GET['sort_by']) ? $_GET['sort_by'] : null,
			'sort_way' => isset($_GET['sort_way']) ? $_GET['sort_way'] : null
		));
		$pagination = new Pagination(array(
			'count' => $count,
			'page' => isset($_GET['page']) ? $_GET['page'] : null,
			'on_page' => isset($_GET['on_page']) ? $_GET['on_page'] : null
		));

		if ($id) {
			$items = $items->where('id', '=', $id);
		} else {
			$items = $items->order_by($sort->get_sort_by(), $sort->get_sort_way());
		}

		if ($this->seo_page->id == Model_Seo::ID_CATALOG_SEO) {
			$items->where('status', '=', Status::STATUS_VISIBLE_VALUE);
		}

		$items = Catalog::selection_and_sorting_orm_items($items, $sort, $pagination);
		$items = $items->find_all()->as_array();
		// ===================================

		$show_add_btn = false;
		if ($this->seo_page->id == Model_Seo::ID_CATALOG_AUTHORS || $this->seo_page->id == Model_Seo::ID_CATALOG_TAGS) {
			$show_add_btn = true;
		}

		if (isset($_GET['id'])) {
			$seo_page = Model_Seo::get_page_by_url($this->request->url());
			$this->seo_data = Model_Seo::get_seo_data_by_page($seo_page);
			$this->seo_data['title'] .= ' - Поиск по id';
			$this->breadcrumbs = $seo_page->get_breadcrumbs();
			$this->breadcrumbs[] = array('url' => '', 'title' => 'Поиск по id');
		}

		$get_params = array();
		return $this->render('catalog/catalog', array(
			'items' => $items,
			'seo_page' => $this->seo_page,
			'catalog_type' => Catalog::CATALOG_TYPE_ADMIN,
			'sort' => $sort,
			'pagination' => $pagination,
			'show_add_btn' => $show_add_btn,
			'get_params' => $get_params
		));
	}

	public function change_status() {
		$item = static::get_orm_object_by_seo_page_id($this->seo_page->id);
		if ($item->loaded()) {

			$status = $_POST['status'];
			if ($status == Status::STATUS_HIDDEN_VALUE && $this->seo_page->id == Model_Seo::ID_CATALOG_AUTHORS) {
				$items_authors = ORM::factory('ItemAuthor')->where('author_id', '=', $item->id)->find_all()->as_array();
				if (count($items_authors)) {
					return $this->render_ajax('Невозможно скрыть автора, так как к нему прикреплена книга. Найдите книгу с таким автором и удалите автора у нее.', Ajax::STATUS_UNSUCCESS);
				}
			}

			$item->status = $status;
			$item->save();
			return $this->render_ajax('ok');
		} else {
			return $this->render_ajax(Messages::DB_ITEM_NOT_FOUND, Ajax::STATUS_UNSUCCESS);
		}
	}

	public function change_title() {
		$item = static::get_orm_object_by_seo_page_id($this->seo_page->id);
		if ($item->loaded()) {
			try {
				$item->title = $_POST['title'];
				$item->save();
				return $this->render_ajax($item->title);
			} catch (ORM_Validation_Exception $e) {
				return $this->render_ajax($e->errors('validation'), Ajax::STATUS_UNSUCCESS);
			}
		} else {
			return $this->render_ajax(Messages::DB_ITEM_NOT_FOUND, Ajax::STATUS_UNSUCCESS);
		}
	}

	public function delete_item() {
		switch ($this->seo_page->id) {
			case Model_Seo::ID_CATALOG_AUTHORS:
				$author = ORM::factory('Author', $_POST['id']);
				if ($author->loaded()) {
					$items_authors = ORM::factory('ItemAuthor')->where('author_id', '=', $author->id)->find_all()->as_array();
					if (count($items_authors)) {
						return $this->render_ajax('Невозможно удалить автора, так как к нему прикреплена книга. Найдите книгу с таким автором и удалите автора у нее.', Ajax::STATUS_UNSUCCESS);
					}
					$author->delete();
					return $this->render_ajax('ok');
				} else {
					return $this->render_ajax(Messages::DB_ITEM_NOT_FOUND, Ajax::STATUS_UNSUCCESS);
				}
				break;
			case Model_Seo::ID_CATALOG_TAGS:
				$tag = ORM::factory('Tag', $_POST['id']);
				if ($tag->loaded()) {
					foreach (ORM::factory('ItemTag')->where('tag_id', '=', $tag->id)->find_all()->as_array() as $item_tag) {
						$item_tag->delete();
					}
					$tag->delete();
					return $this->render_ajax('ok');
				} else {
					return $this->render_ajax(Messages::DB_ITEM_NOT_FOUND, Ajax::STATUS_UNSUCCESS);
				}
				break;
		}
	}

	public function create_item() {
		$item = static::get_orm_object_by_seo_page_id($this->seo_page->id);
		if ($item->loaded() && $_POST['id'] || !isset($_POST['id'])) {
			try {
				if (!in_array($_POST['status'], array_keys(Status::STATUSES_HV))) {
					return $this->render_ajax(Messages::STATUS_INCORRECT, Ajax::STATUS_UNSUCCESS);
				}
				$item->status = $_POST['status'];
				$item->title = $_POST['title'];
				$item->save();
				return $this->render_ajax(Catalog::render_item_admin($this->seo_page, $item));
			} catch (ORM_Validation_Exception $e) {
				return $this->render_ajax($e->errors('models'), Ajax::STATUS_UNSUCCESS);
			}
		} else {
			return $this->render_ajax(Messages::DB_ITEM_NOT_FOUND, Ajax::STATUS_UNSUCCESS);
		}
	}

	static public function get_orm_object_by_seo_page_id($seo_page_id) {
		switch ($seo_page_id) {
			case Model_Seo::ID_CATALOG_AUTHORS:
				$item = ORM::factory('Author');
				break;
			case Model_Seo::ID_CATALOG_TAGS:
				$item = ORM::factory('Tag');
				break;
		}

		if (isset($_POST['id'])) {
			$item->where('id', '=', $_POST['id']);
			$item->find();
		}
		return $item;
	}

}
