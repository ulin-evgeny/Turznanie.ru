<?php

class Controller_Catalog extends Controller {

	use Trait_Tag, Trait_Author;

	public $material;

	public function before() {
		parent::before();
		$this->material = Catalog::get_material_type_catalog_by_alias($this->request->param('alias'));
	}

	/**
	 * Вместо action в данном контроллере функции вызываются за счет первого параметра контроллера (смотри bootstrap). В данной функции как раз разбираются эти параметры и вызываются соответствующие функции.
	 * Кроме того, здесь происходят различные проверки. Проверка доступа, например - может ли посетитель просматривать  публикацию, менять ее рейтинг. Почему это сделано здесь, а не, собственно, в функциях для просмтра публикации и изменения рейтинга? Потому что эти проверки повторяются. И проще написать их в одном месте, чем дублировать код в каждую функцию.
	 * @param $page
	 * @return mixed|void
	 */
	public function local_routing($page) {
		// =========================================
		// Методы без проверок
		// =========================================
		switch ($page) {
			case 'tag_autocomplete':
				return $this->tag_autocomplete();
				break;
			case 'tag_status':
				return $this->tag_status();
				break;
			case 'author_autocomplete':
				return $this->author_autocomplete();
				break;
		}

		$func_and_args = array();
		$func_and_args['args'] = array();
		$error = false;

		// =========================================
		// Проверка существования записи в БД
		// =========================================
		switch (true) {
			// --------------------------------------
			// Item
			// --------------------------------------
			case (in_array($page, array('comment_add', 'rating_change', 'delete', 'edit', 'item_favorite'))):
				if (in_array($page, array('edit'))) {
					$id = $this->request->param('part2');
				} else {
					$id = $_POST['item_id'];
				}
				$item = ORM::factory('Item', $id);
				if (!$item->loaded()) {
					$error = true;
				}
				break;
			// --------------------------------------
			// ItemComment
			// --------------------------------------
			case (in_array($page, array('comment_delete', 'comment_edit', 'comment_change_status'))):
				$item_comment = ORM::factory('ItemComment', $_POST['id']);
				if (!$item_comment->loaded()) {
					$error = true;
				}
				break;
		}

		if ($error) {
			return $this->render_ajax(Messages::DB_ITEM_NOT_FOUND, Ajax::STATUS_UNSUCCESS);
		}

		// =========================================
		// Проверка прав доступа и установка метода, который будет вызван
		// =========================================
		// --------------------------------------
		// Для пользователя с подтвержденным Email
		// --------------------------------------
		switch (true) {
			case (in_array($page, array('new', 'comment_add', 'rating_change'))):
				if (CurrentUser::get_user()->is_approved()) {
					switch ($page) {
						case 'new':
							if (
								(
									(
										$this->material->id == Model_Item::MATERIAL_LITERATURE ||
										$this->material->id == Model_Item::MATERIAL_NEWS
									) &&
									CurrentUser::get_user()->is_admin()
								) ||
								$this->material->id == Model_Item::MATERIAL_ARTICLE) {
								$func_and_args['func'] = 'filling';
							} else {
								$error = true;
							}
							break;
						case 'comment_add':
							$func_and_args['func'] = 'comment_add';
							break;
						case 'rating_change':
							$func_and_args['func'] = 'rating_change';
							break;
					}
				} else {
					$error = true;
				}
				break;
			// --------------------------------------
			// Для владельца комментария / админа
			// --------------------------------------
			case (in_array($page, array('comment_delete', 'comment_edit'))):
				$comment = ORM::factory('ItemComment', $_POST['id']);
				if (CurrentUser::get_user()->can_edit_comment($comment)) {
					switch ($page) {
						case 'comment_delete':
							$func_and_args['func'] = 'comment_delete';
							break;
						case 'comment_edit':
							$func_and_args['func'] = 'comment_edit';
							break;
					}
				} else {
					$error = true;
				}
				break;
			// --------------------------------------
			// Для владельца item'а / админа
			// --------------------------------------
			case (in_array($page, array('delete', 'edit'))):
				switch ($page) {
					case 'delete':
						$id = $_POST['item_id'];
						break;
					case 'edit':
						$id = $this->request->param('part2');
						break;
				}
				$item = ORM::factory('Item', $id);
				if (CurrentUser::get_user()->can_edit_item($item)) {
					switch ($page) {
						case 'delete':
							$func_and_args['func'] = 'delete';
							break;
						case 'edit':
							$func_and_args['func'] = 'filling';
							$func_and_args['args'] = array($item->id);
							break;
					}
				} else {
					$error = true;
				}
				break;
			// --------------------------------------
			// Для админа
			// --------------------------------------
			case (in_array($page, array('comment_change_status'))):
				if (CurrentUser::get_user()->is_admin()) {
					$func_and_args['func'] = 'comment_change_status';
				} else {
					$error = true;
				}
				break;
			// --------------------------------------
			// Для зарегистрированного пользователя (не обязательно с подтвержденным Email)
			// --------------------------------------
			case (in_array($page, array('item_favorite'))):
				if ($this->user->loaded()) {
					$func_and_args['func'] = 'item_favorite';
				} else {
					$error = true;
				}
				break;
			default:
				return $this->action_index(true);
				break;
		}

		if ($error) {
			return $this->render_ajax(Messages::NOT_ENOUGH_RULES, Ajax::STATUS_UNSUCCESS);
		}

		// =========================================
		// Другие разные проверки со своими ошибками и сообщениями об ошибках
		// =========================================
		// --------------------------------------
		// Проверка на доступ к скрытой публикации
		// --------------------------------------
		if (in_array($page, array(
			'rating_change',
			'item_favorite',
			'comment_add',
			'comment_edit',
			'comment_delete',
			'comment_change',
			'comment_change_status'
		))) {
			// получение Item'а
			switch (true) {
				case in_array($page, array('item_favorite', 'rating_change', 'comment_add')):
					$item = ORM::factory('Item', $_POST['item_id']);
					break;
				case in_array($page, array(
					'comment_edit',
					'comment_delete',
					'comment_change',
					'comment_change_status')):
					$comment = ORM::factory('ItemComment', $_POST['id']);
					$item = ORM::factory('Item', $comment->item_id);
					break;
			}

			// Проверка прав - все доступно админу. И только комментирование доступно для пользователя. Это нужно, чтобы админ мог связаться с пользователем - у пользователя ведь рассылка есть, когда кто-то оставляет сообщение к его статье. В скрытых Item'ах админ и владелец скрытого Item'а могут вести переписку по правкам.
			if ($item->status != Status::STATUS_VISIBLE_VALUE) {
				if (!CurrentUser::get_user()->is_admin() && !($item->have_access_to_hidden() && in_array($page, array(
							'comment_add',
							'comment_edit',
							'comment_delete'
						)))) {
					$error = true;
				}
			}
			if ($error) {
				return $this->render_ajax('Невозможно произвести действие, так как публикация скрыта.', Ajax::STATUS_UNSUCCESS);
			}
		}

		return call_user_func_array(array($this, $func_and_args['func']), $func_and_args['args']);
	}

	public function action_index($skip_local_routing = false) {
		// ---------------------------------
		// Роутинг внутри класса
		// ---------------------------------
		$page = $this->request->param('part1');
		if ($page && !$skip_local_routing) {
			return $this->local_routing($page);
		}

		// ---------------------------------
		// Установка breadcrumbs
		// ---------------------------------
		$this->seo_page = Model_Seo::get_page_by_url(Request::initial()->url());
		if (!$this->seo_page->loaded()) {
			$this->go_back();
		}

		/*
		Поскольку разные категории (рецензии, советы) - это просто url параметры одной страницы (одной записи в БД - страницы каталога), то breadcrumbs нужно доделывать вручную - функция get_breadcrumbs у Model_Seo не подходит.
		Сначала Находим breadcrumbs для материала (ведь для него можно найти - это ведь Model_Seo). После чего будем добавлять к нему breadcrumbs каталогов - на основе текущего url.
		*/
		$this->breadcrumbs = $this->seo_page->get_breadcrumbs(false);
		$url_parts = explode('/', $this->request->url());
		$count_parts = count($url_parts);

		// Убираем id item'a - если это страница просмотра item'а. И считаем количество parts без него.
		if (is_numeric($url_parts[$count_parts - 1])) {
			$count_bc = count($this->breadcrumbs);
			unset($this->breadcrumbs[$count_bc - 1]);
			unset($url_parts[$count_parts - 1]);
		}

		// Убираем пустой элемент - он первый (ведь url начинается со знака "/", а explode берет значение и до него)
		unset($url_parts[0]);
		// Убираем родителя, так как он и так есть в breadcrumbs
		unset($url_parts[1]);

		// Если от url что-то осталось кроме alias'а материала, то создаем из этого breadcrumbs.
		if (count($url_parts) > 0) {
			$url = $this->material->get_url();
			$title = '';
			foreach ($url_parts as $key => $url_part) {
				$last_catalog = ORM::factory('Catalog')->where('alias', '=', $url_part)->find();
				$url .= '/' . $last_catalog->alias;
				$title .= ' - ' . $last_catalog->title;
				$breadcrumbs[$key]['title'] = $last_catalog->title;
				$breadcrumbs[$key]['url'] = $url;
			}

			$this->breadcrumbs = array_merge($this->breadcrumbs, $breadcrumbs);
		} else {
			$last_catalog = $this->material;
		}

		// ---------------------------------
		// Если это страница item'а, а не каталога
		// ---------------------------------
		$url_parts = explode('/', $this->request->url());
		$count_url_parts = count($url_parts);
		if ($count_url_parts > 2) {
			$last_url_part = $url_parts[$count_url_parts - 1];
			if (is_numeric($last_url_part)) {
				$item = ORM::factory('Item', $last_url_part);
				if ($item->loaded()) {
					if ($item->have_access_to_hidden()) {
						return $this->render_item($item);
					} else {
						return $this->render('pages/message', array(
								'text' => 'Вы не можете просматривать данную публикацию, так как она скрыта.'
							)
						);
					}
				} else {
					return $this->render('pages/message', array(
						'text' => Messages::DB_ITEM_NOT_FOUND
					));
				}
			}
		}

		// ===================================
		// Установка get_params, получение $sort, $pagination и $items
		// ===================================
		$get_params = [];

		// Фильтрация по тегу
		if (isset($_GET['tag'])) {
			$get_params['tag'] = $_GET['tag'];
			$purifier = HelperHTMLPurifier::get_purifier();
			$get_params['tag'] = $purifier->purify($get_params['tag']);
			if (!$get_params['tag']) {
				unset($get_params['tag']);
			}
		}

		// -----------------------------------
		// Получение $sort
		// -----------------------------------
		$sort = new Sort(array(
			'seo_id' => $this->seo_page->id,
			'sort_by_default' => Sort::SORT_BY_DATE,
			'sort_by' => isset($_GET['sort_by']) ? $_GET['sort_by'] : null,
			'sort_way' => isset($_GET['sort_way']) ? $_GET['sort_way'] : null
		));

		// ------------------------------------------------
		// Вычисление параметров фильтрации по дате и страницам
		// ------------------------------------------------
		// Фильтрация по дате (есть у статей и новостей)
		if ($this->material->id == Model_Item::MATERIAL_ARTICLE || $this->material->id == Model_Item::MATERIAL_NEWS) {
			if (isset($_GET['date_from'])) {
				$date_from = intval($_GET['date_from']);
				if ($date_from) {
					$get_params['date_from'] = $date_from;
				}
			}
			if (isset($_GET['date_to'])) {
				$date_to = intval($_GET['date_to']);
				if ($date_to) {
					$get_params['date_to'] = $date_to;
				}
			}
		}

		// Фильтрация по страницам (есть только у литературы)
		if ($this->material->id == Model_Item::MATERIAL_LITERATURE) {
			if (isset($_GET['pages_from'])) {
				$pages_from = intval($_GET['pages_from']);
				if ($pages_from) {
					$get_params['pages_from'] = $pages_from;
				}
			}
			if (isset($_GET['pages_to'])) {
				$pages_to = intval($_GET['pages_to']);
				if ($pages_to) {
					$get_params['pages_to'] = $pages_to;
				}
			}
		}

		// -------------------------------------------------
		// Выборка item'ов
		// -------------------------------------------------
		foreach ($last_catalog->get_children(true) as $c) {
			$catalog_ids[] = $c->id;
		}

		$get_params = array_merge($get_params, Catalog::get_items_params($catalog_ids, $get_params));

		$pagination = new Pagination(array(
			'count' => $get_params['current_count'],
			'page' => isset($_GET['page']) ? $_GET['page'] : null,
			'on_page' => isset($_GET['on_page']) ? $_GET['on_page'] : null
		));

		$items_and_params = Catalog::get_items_and_params($catalog_ids, $sort, $pagination, $get_params);
		$items = $items_and_params['items'];
		$get_params = array_merge($get_params, $items_and_params['params']);
		// ===================================

		$render_statusbar = false;
		if ($this->user && $this->user->has_role(array(Model_Role::ID_ADMIN, Model_Role::ID_SUPERADMIN))) {
			$render_statusbar = true;
		}

		if ($this->request->is_ajax()) {
			$this->template_main = static::TEMPLATE_AJAX;
		}

		// Установка $show_add_btn и $btn_add_href
		$show_add_btn = false;
		if (($this->material->id == Model_Item::MATERIAL_LITERATURE || $this->material->id == Model_Item::MATERIAL_NEWS) && $this->user->is_admin()) {
			$show_add_btn = true;

			switch ($this->material->id) {
				case Model_Item::MATERIAL_LITERATURE:
					$add_alias = ORM::factory('Seo', Model_Seo::ID_ADD_LITERATURE)->alias;
					break;
				case Model_Item::MATERIAL_NEWS:
					$add_alias = ORM::factory('Seo', Model_Seo::ID_ADD_NEWS)->alias;
					break;
			}
		} elseif ($this->material->id == Model_Item::MATERIAL_ARTICLE && CurrentUser::get_user()->is_approved()) {
			$show_add_btn = true;
			$add_alias = ORM::factory('Seo', Model_Seo::ID_ADD_ARTICLE)->alias;
		}

		if ($show_add_btn) {
			$btn_add_href = $this->material->alias;
			$btn_add_href = '/' . $btn_add_href . '/' . $add_alias;
		}

		return $this->render('catalog/catalog', array(
			'last_catalog' => $last_catalog,
			'items' => $items,
			'get_params' => $get_params,
			'material' => $this->material,
			'render_statusbar' => $render_statusbar,
			'catalog_type' => Catalog::CATALOG_TYPE_NORMAL,
			'sort' => $sort,
			'pagination' => $pagination,
			'btn_add_href' => isset($btn_add_href) ? $btn_add_href : null,
			'show_add_btn' => $show_add_btn
		));
	}

	public function item_favorite() {
		$item_id = $_POST['item_id'];
		$value = $_POST['favorite'];
		$user = CurrentUser::get_user();
		$favorites = ORM::factory('ItemFavorite')
			->where('user_id', '=', $user->id)
			->where('item_id', '=', $item_id)
			->find();
		$favorites->user_id = $user->id;
		$favorites->item_id = $item_id;
		try {
			if ($value) {
				$favorites->save();
			} else {
				$favorites->delete();
			}
		} catch (Exception $e) {
			return false;
		}

		return $this->render_ajax('ok');
	}

	public function rating_change() {
		$item_id = $_POST['item_id'];
		$item = ORM::factory('Item', $item_id);
		if (CurrentUser::get_user()->is_item_author($item)) {
			return $this->render_ajax('Нельзя менять рейтинг своих публикаций', Ajax::STATUS_UNSUCCESS);
		}

		$rate = $_POST['rate'];
		$item_rating = ORM::factory('ItemRating')
			->where('user_id', '=', $this->user->id)
			->where('item_id', '=', $item_id)
			->find();

		try {
			if ($rate === '0' && $item_rating->loaded()) {
				$item_rating->delete();
			} else {
				$item_rating->user_id = $this->user->id;
				$item_rating->item_id = $item_id;
				$item_rating->rate = $rate;
				$item_rating->save();
			}
		} catch (ORM_Validation_Exception $e) {
			return $this->render_ajax($e->errors('models'), Ajax::STATUS_UNSUCCESS);
		}

		$item = ORM::factory('Item', $item_id);
		$result = Widget_ItemParams::render_rating($item);

		return $this->render_ajax($result);
	}

	public function comment_delete() {
		$comment = ORM::factory('ItemComment', $_POST['id']);
		try {
			$comment->delete();
		} catch (ORM_Validation_Exception $e) {
			return $this->render_ajax($e->errors('models'), Ajax::STATUS_UNSUCCESS);
		}

		return $this->render_ajax('ok');
	}

	public function comment_edit() {
		$comment = ORM::factory('ItemComment', $_POST['id']);
		try {
			$comment->save_comment(true);
		} catch (ORM_Validation_Exception $e) {
			return $this->render_ajax($e->errors('models'), Ajax::STATUS_UNSUCCESS);
		} catch (CaptchaException $e) {
			return $this->render_ajax(HelperReCaptcha::render(null), Ajax::STATUS_NEED_CAPTCHA);
		}

		$edited_comment = ORM::factory('ItemComment', $comment->id);
		return $this->render_ajax(array(
			'comment_id' => $edited_comment->id,
			'comment' => Widget_Comments::render_comment($edited_comment, $this->user)
		));
	}

	public function comment_add() {
		$comment = ORM::factory('ItemComment');
		try {
			$comment->save_comment();
		} catch (ORM_Validation_Exception $e) {
			return $this->render_ajax($e->errors('models'), Ajax::STATUS_UNSUCCESS);
		} catch (CaptchaException $e) {
			return $this->render_ajax(HelperReCaptcha::render(null), Ajax::STATUS_NEED_CAPTCHA);
		}

		// если заново не загружать запись, то date будет равен null
		$comment = ORM::factory('ItemComment', $comment->id);
		return $this->render_ajax(Widget_Comments::render_comment($comment, $this->user));
	}

	public function comment_change_status() {
		$comment = ORM::factory('ItemComment', $_POST['id']);
		try {
			$comment->change_status();
		} catch (ORM_Validation_Exception $e) {
			return $this->render_ajax($e->errors('models'), Ajax::STATUS_UNSUCCESS);
		}

		$edited_comment = ORM::factory('ItemComment', $comment->id);
		return $this->render_ajax(array(
			'comment_id' => $edited_comment->id,
			'comment' => Widget_Comments::render_comment($edited_comment, $this->user)
		));
	}

	// ===============================================
	// Функции, касающиеся Item'а
	// ===============================================
	public function delete() {
		// ---------------------------------
		// удаляем записи из связанных таблиц
		// ---------------------------------
		$item = ORM::factory('Item', $_POST['item_id']);
		foreach (ORM::factory('ItemFavorite')->where('item_id', '=', $item->id)->find_all()->as_array() as $row) {
			$row->delete();
		}
		foreach (ORM::factory('ItemComment')->where('item_id', '=', $item->id)->find_all()->as_array() as $row) {
			$row->delete();
		}
		foreach (ORM::factory('ItemRating')->where('item_id', '=', $item->id)->find_all()->as_array() as $row) {
			$row->delete();
		}
		foreach (ORM::factory('ItemTag')->where('item_id', '=', $item->id)->find_all()->as_array() as $row) {
			$row->delete();
		}
		foreach (ORM::factory('ItemAuthor')->where('item_id', '=', $item->id)->find_all()->as_array() as $row) {
			$row->delete();
		}

		// ---------------------------------
		// удаляем файлы
		// ---------------------------------
		$material_type = $item->get_material_catalog()->id;
		switch (true) {
			case $material_type == Model_Item::MATERIAL_ARTICLE || $material_type == Model_Item::MATERIAL_NEWS:
				if ($item->photo) {
					PhotoSizepackUploader::delete_photo_sizepack(Model_Item::get_images_path(Model_Item::FOLDER_ARTICLES), $item->photo, Model_Item::PHOTO_SIZES_ARTICLE);
				}
				break;
			case $material_type == Model_Item::MATERIAL_LITERATURE:
				if ($item->photo) {
					PhotoSizepackUploader::delete_photo_sizepack(Model_Item::get_images_path(Model_Item::FOLDER_LITERATURE), $item->photo, Model_Item::PHOTO_SIZES_LITERATURE);
				}
				$books_folder = Model_Item::PATH_FILES . Model_Item::FOLDER_LITERATURE . Model_Item::FOLDER_BOOKS . $item->id;
				if (file_exists($books_folder)) {
					Helper::delete_directory($books_folder);
				}
				break;
		}

		// ---------------------------------
		// удаляем сам материал и выводим сообщение
		// ---------------------------------
		$item->delete();
		return $this->render_ajax(new PageMessage(array(
			'text' => 'Материал успешно удален!',
			'btn_href' => $_POST['url'],
			'btn_text' => 'Назад'
		)));
	}


	public function filling($item_id = null) {
		$filling_item = ORM::factory('item', $item_id);
		if ($_POST) {
			if ($item_id) {
				return $this->save_item($filling_item);
			} else {
				return $this->save_item();
			}
		}

		return $this->render('catalog/item_filling', array(
			'material' => ORM::factory('Catalog', $this->material->id),
			'is_changing' => $item_id ? true : false,
			'filling_item' => $filling_item
		));
	}

	public function save_item($filling_item = false) {
		if ($filling_item) {
			$is_changing = true;
		} else {
			$is_changing = false;
			$filling_item = ORM::factory('Item');
		}
		$filling_item_material = $this->material->id;

		// ====================================================
		//	Валидация и установка значений
		// ====================================================
		$validation = new Validation($_POST);
		// try сразу, а не только в save(), так как по пути мы чекаем дополнительные модели - автора для литературы, например, теги.
		try {
			// ----------------------------
			// Статус
			// ----------------------------
			if (CurrentUser::get_user()->is_admin()) {
				$filling_item->status = $_POST['status'];
			} else {
				$filling_item->status = Status::STATUS_HIDDEN_VALUE;
			}

			// ----------------------------
			// Страницы
			// ----------------------------
			$filling_item->pages = isset($_POST['pages']) ? $_POST['pages'] : 0;

			// ----------------------------
			// Пользователь, который добавил публикацию
			// ----------------------------
			if (!$is_changing) {
				$filling_item->user_id = $this->user->id;
			}

			// ----------------------------
			// Раздел публикации
			// ----------------------------
			$filling_item->catalog_id = $_POST['catalog_id'];

			// ----------------------------
			// Название публикации
			// ----------------------------
			$filling_item->name = $_POST['name'];

			// ----------------------------
			// Фотография (если она загружена)
			// ----------------------------
			if ($_POST['photo_changed'] == 1 && $_FILES['photo']['size'] != 0) {
				$dont_upload_photo = PhotoSizepackUploader::find_validation_errors(Model_Item::PHOTO_EXTENSIONS, Model_Item::MAX_PHOTO_SIZE);
				if ($dont_upload_photo) {
					return $this->render_ajax(array('message' => $dont_upload_photo), Ajax::STATUS_UNSUCCESS);
				}
			} else {
				$dont_upload_photo = true;
			}

			// ----------------------------
			// Описание книги / текст статьи
			// ----------------------------
			$filling_item->description = $_POST['description'];

			// ----------------------------
			// Теги
			// ----------------------------
			// Количество тегов (если пользователь как-то обошел валиацию на клиенте и добавил больше 5 тегов)
			$filling_item_tags_strs = str_replace(' ', '', $_POST['tags']);
			$filling_item_tags_strs = explode(',', $filling_item_tags_strs);
			$filling_item_tags_strs = array_filter($filling_item_tags_strs);
			if (count($filling_item_tags_strs) > 5) {
				return $this->render_ajax('Задано больше 5 тегов', Ajax::STATUS_UNSUCCESS);
			}

			// Если такого тега нет в БД, то создаем новый и валидируем его (на длину, на еще какие его rules).
			$filling_item_tags = [];
			foreach ($filling_item_tags_strs as $tag_title) {
				$tag = ORM::factory('Tag')->where('title', '=', $tag_title)->find();
				if (!$tag->loaded()) {
					$tag->title = $tag_title;
					$tag->check();
				}
				$filling_item_tags[] = $tag;
			}

			// ------------------------------------------------------
			// Авторы (только для литературы)
			// ------------------------------------------------------
			if ($this->material->id == Model_Item::MATERIAL_LITERATURE) {
				$filling_items_authors = [];
				$authors = $_POST['authors'];
				foreach ($authors as $key => $val) {
					if (!mb_strlen($val)) {
						unset($authors[$key]);
						continue;
					}
					$author = ORM::factory('Author')->where('title', '=', $val)->find();
					if (!$author->loaded()) {
						$author->title = $val;
						$author->check();
					}
					if ($filling_item->status == Status::STATUS_VISIBLE_VALUE) {
						$author->status = Status::STATUS_VISIBLE_VALUE;
					}
					$filling_items_authors[] = $author;
				}
				if (!count($authors)) {
					return $this->render_ajax('Нужно указать, по крайней мере, одного автора', Ajax::STATUS_UNSUCCESS);
				}
			}

			// ------------------------------------------------------
			// Превью (только для статьи или новости)
			// ------------------------------------------------------
			if ($this->material->id == Model_Item::MATERIAL_ARTICLE || $filling_item_material == Model_Item::MATERIAL_NEWS) {
				$preview_maximum_length = $filling_item->table_columns()['preview']['character_maximum_length'];

				$filling_item->preview = HelperText::super_trim(strip_tags($_POST['preview']));
				if (!mb_strlen($filling_item->preview)) {
					// подготовка превью
					$preview = $filling_item->description;

					// добавляем пробелы между переносами строк, которые осуществляются за счет тегов, которые потом будут удалены (читай код ниже - strip_tags), что и создает такую необходимость.
					$preview = preg_replace('/(<\/p>)((<p)|(<br))/i', '$1 $2', $preview);
					$preview = HelperText::super_trim(strip_tags($preview));

					// важно! в функции multiple_space_to также удаляются 160 пробелы (которые генерирует ckeditor) и просто так их не удалить. если будешь убирать эту функцию, имей в виду.
					$preview = HelperText::multiple_space_to($preview);

					// применяем HTMLpurifier. это чтобы длина preview была ровно столько, сколько нужно - не меньше и не больше. ведь он (purifier) применяется при сохранении (смотри save() в ORM). и он может изменить строку.
					$purifier = HelperHTMLPurifier::get_purifier();
					$preview = $purifier->purify($preview);

					$preview = trim(strip_tags($preview));

					$preview_length = mb_strlen($preview);
					if ($preview_length > $preview_maximum_length) {
						$preview = mb_substr($preview, 0, $preview_maximum_length - 3) . '...';
					}
					$filling_item->preview = $preview;
				} else {
					$validation->label('preview', 'Превью');
				}
			}

			// --------------------------------------------------------
			// Книги (для литературы) и установка некоторых значений для их сохранения
			// --------------------------------------------------------
			if ($this->material->id == Model_Item::MATERIAL_LITERATURE) {
				$filling_item_books = $_FILES['books'];
				$filling_item_to_delete_string = $_POST['custom_uploader_to_delete'];
				// Для удобства работы с файлами преобразуем их в нужный массив
				$filling_item_books = Helper::get_objects_array_from_fields_array($filling_item_books);
				// Эти действия нужны, так как в плагине в JS у custom-uploader__template есть input, у еще недобавленного файла тоже есть input (кнопка "Добавить файл" вызывает щелчок по нему).
				foreach ($filling_item_books as $key => $val) {
					if ($val['size'] === 0) {
						unset($filling_item_books[$key]);
					}
				}
				$filling_item->validate_books($filling_item_to_delete_string, $filling_item_books);
			}
			// --------------------------------------------------------

			$filling_item->check_with_captcha($validation);
		} catch (ORM_Validation_Exception $e) {
			$errors = $e->errors('models');
			if (isset($errors['description'])) {
				$errors['description'] = str_replace('description', Model_Item::get_description_label($this->material->id), $errors['description']);
			}
			return $this->render_ajax($errors, Ajax::STATUS_UNSUCCESS);
		} catch (CaptchaException $e) {
			return $this->render_ajax(HelperReCaptcha::render(null), Ajax::STATUS_NEED_CAPTCHA);
		}

		// ====================================================
		// Сохранение Item
		// ====================================================
		// Пока еще не сохранили, получаем старые значения (чтобы узнать - нужно ли отправлять письмо (если изменился status))
		if ($is_changing) {
			$filling_item_old = ORM::factory('Item', $filling_item->id);
		}

		// Отправка письма - уведомления о том, что изменен статус статьи
		if (CurrentUser::get_user()->is_admin() &&
			$is_changing &&
			$filling_item_old->status != $filling_item->status
		) {
			$item_user = ORM::factory('User', $filling_item->user_id);
			if ($item_user->has_notification('material_status')) {
				$status_string = Status::STATUSES_HV[$filling_item->status];
				$url = Helper::get_site_url() . $filling_item->get_url();
				$mail = new CustomEmail();
				$mail->addAddress($item_user->email, $item_user->username);
				$mail->set_subject_and_body('item_change_status', array('status_string' => $status_string, 'url' => $url));
				$mail->send(array('unsubscribe' => true));
			}
		}

		$filling_item->save();

		// =================================================
		// Создание автора и тегов (это делается после сохранения Item'а - потому что нам нужен его id
		// =================================================
		// ---------------------------------------
		// Создание авторов и сохранение книг (только для литературы)
		// ---------------------------------------
		if ($this->material->id == Model_Item::MATERIAL_LITERATURE) {
			// ---------------------------------
			// Author
			// ---------------------------------
			$new_authors = [];
			foreach ($filling_items_authors as $author) {
				$author->save();
				$new_authors[$author->id] = $author;
			}

			// ---------------------------------
			// ItemAuthor
			// ---------------------------------
			// Удаляем ненужные
			if (!empty($new_authors)) {
				$items_authors_to_delete = ORM::factory('ItemAuthor')
					->where('item_id', '=', $filling_item->id)
					->where('author_id', 'not in', array_keys($new_authors))
					->find_all()
					->as_array();
			} else {
				$items_authors_to_delete = ORM::factory('ItemAuthor')
					->where('item_id', '=', $filling_item->id)
					->find_all()
					->as_array();
			}
			foreach ($items_authors_to_delete as $ia) {
				$ia->delete();
			}

			// Сохраняем нужные
			foreach ($new_authors as $author) {
				$item_author = ORM::factory('ItemAuthor')->where('item_id', '=', $filling_item->id)->where('author_id', '=', $author->id)->find();

				// Если такая запись уже есть, то ее сохранять не нужно
				if (!$item_author->loaded()) {
					$item_author->item_id = $filling_item->id;
					$item_author->author_id = $author->id;
					$item_author->save();
				}
			}

			// -------------------------------------------
			// Книги
			// -------------------------------------------
			if ($this->material->id == Model_Item::MATERIAL_LITERATURE) {
				if ((isset($_POST['custom_uploader_has_been_change']) && $_POST['custom_uploader_has_been_change']) || !mb_strlen($filling_item->files)) {
					$filling_item->delete_and_save_books($filling_item_to_delete_string, $filling_item_books);
				}
			}
		}

		// ---------------------------------
		// Работа с фоткой
		// ---------------------------------
		// Сохранение фотки
		if (!$dont_upload_photo || PhotoSizepackUploader::is_deleting($dont_upload_photo) && $filling_item->photo != '') {
			if ($filling_item_material == Model_Item::MATERIAL_LITERATURE) {
				$folder = Model_Item::FOLDER_LITERATURE;
				$photo_sizes = Model_Item::PHOTO_SIZES_LITERATURE;
			} else {
				$folder = Model_Item::FOLDER_ARTICLES;
				$photo_sizes = Model_Item::PHOTO_SIZES_ARTICLE;
			}
			$path = Model_Item::get_images_path($folder);
			if (!$dont_upload_photo) {
				if ($filling_item->photo == '') {
					$photo = PhotoSizepackUploader::save_photo_sizepack($path, $filling_item->id . time(), $photo_sizes);
				} else {
					$photo = PhotoSizepackUploader::change_photo_sizepack($path, $filling_item->id . time(), $filling_item->photo, $photo_sizes);
				}
				$filling_item->photo = $photo;
			} // Удаление фотки
			elseif (PhotoSizepackUploader::is_deleting($dont_upload_photo) && $filling_item->photo != '') {
				PhotoSizepackUploader::delete_photo_sizepack($path, $filling_item->photo, $photo_sizes);
				$filling_item->photo = '';
			}
			$filling_item->save();
		}

		// ---------------------------------
		// Теги
		// ---------------------------------
		$new_tags = [];
		foreach ($filling_item_tags as $tag) {
			if (!$tag->loaded()) {
				$tag->save();
			}
			$new_tags[$tag->id] = $tag;
		}

		// Удаляем ненужные
		if (!empty($new_tags)) {
			$items_tags_to_delete = ORM::factory('ItemTag')
				->where('item_id', '=', $filling_item->id)
				->where('tag_id', 'not in', array_keys($new_tags))
				->find_all()
				->as_array();
		} else {
			$items_tags_to_delete = ORM::factory('ItemTag')
				->where('item_id', '=', $filling_item->id)
				->find_all()
				->as_array();
		}
		foreach ($items_tags_to_delete as $it) {
			$it->delete();
		}

		// Сохраняем нужные
		foreach ($new_tags as $tag) {
			$item_tag = ORM::factory('ItemTag')->where('item_id', '=', $filling_item->id)->where('tag_id', '=', $tag->id)->find();

			// Если такая запись уже есть, то ее сохранять не нужно
			if (!$item_tag->loaded()) {
				$item_tag->item_id = $filling_item->id;
				$item_tag->tag_id = $tag->id;
				$item_tag->save();
			}
		}
		// ---------------------------------

		if ($is_changing) {
			if ($this->user->is_admin()) {
				$text = 'Изменения сохранены!';
			} else {
				$text = 'Ваша статья с новыми изменениями будет видна после проверки модератором.';
			}
		} else {
			if ($filling_item_material == Model_Item::MATERIAL_ARTICLE) {
				if ($this->user && $this->user->has_role(array(Model_Role::ID_SUPERADMIN, Model_Role::ID_ADMIN))) {
					$text = 'Сохранено!';
				} else {
					$text = 'Поздравляем! Ваша статья готова. Но перед публикацией ее должен проверить модератор. Мы оповестим вас по Email о результате.';
				}
				$filling_item->get_url();
			} elseif ($filling_item_material == Model_Item::MATERIAL_LITERATURE || $filling_item_material == Model_Item::MATERIAL_NEWS) {
				$text = 'Сохранено!';
			}
		}

		return $this->render_ajax(
			new PageMessage(array(
				'text' => $text,
				'btn_text' => 'Посмотреть',
				'btn_href' => $filling_item->get_url()
			))
		);
	}

	public function render_item($item) {
		switch ($this->material->id) {
			case Model_Item::MATERIAL_LITERATURE:
				$view = 'item_literature';
				$photo = Helper::const_to_client($item->get_photo_by_size(Model_Item::PHOTO_SIZES_LITERATURE['sm'], Model_Item::FOLDER_LITERATURE));
				break;
			case ($this->material->id == Model_Item::MATERIAL_ARTICLE || $this->material->id == Model_Item::MATERIAL_NEWS):
				$view = 'item_article';
				if ($item->photo) {
					$photo = Helper::const_to_client($item->get_photo_by_size(Model_Item::PHOTO_SIZES_ARTICLE['sm'], Model_Item::FOLDER_ARTICLES, true));
				} else {
					$photo = false;
				}
				break;
		}
		$title = $item->name;

		// --------------------------------------------
		// Установка breadcrumbs и seo_data['title']
		// --------------------------------------------
		$count_bc = count($this->breadcrumbs);
		$this->breadcrumbs[$count_bc]['title'] = $title;
		$this->breadcrumbs[$count_bc]['url'] = $item->get_url();
		$this->seo_data['title'] = $title;
		// --------------------------------------------

		$item->views = $item->views + 1;
		$item->save();

		//History::set($item->id);

		$comments = ORM::factory('ItemComment')
			->where('item_id', '=', $item->id)
			->where('status', '=', 1)
			->find_all();

		return $this->render('catalog/' . $view, array(
			'item' => $item,
			'comments' => $comments,
			'material' => $this->material,
			'photo' => $photo
		));
	}

}