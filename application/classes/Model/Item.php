<?php

class Model_Item extends ORM {

	use Trait_Photo;

	public function rules() {
		return array(
			'catalog_id' => array(
				array('not_empty'),
				array(function ($value, $validation) {
					// получение возможных значений
					$material_type = Catalog::get_material_type_catalog_by_alias(Request::initial()->param('alias'));
					$descendants = $material_type->get_descendants();
					foreach ($descendants as $d) {
						$descendants_ids[] = $d->id;
					}
					// сама валидация
					$filling_item_catalog_id = $value;
					if ($filling_item_catalog_id == 0 || !in_array($filling_item_catalog_id, $descendants_ids)) {
						return $validation->error('catalog_id', 'incorrect');
					}
					if (in_array($filling_item_catalog_id, $descendants_ids)) {
						$filling_item_catalog = ORM::factory('Catalog', $filling_item_catalog_id);
						if (!$filling_item_catalog->loaded()) {
							return $validation->error('catalog_id', 'incorrect');
						}
					}
					return true;
				}, array(':value', ':validation'))
			),
			'name' => array(
				array('not_empty'),
				array('min_length', array(':value', Search::MIN_SEARCH_LENGTH))
			),
			'description' => array(
				array('not_empty')
			),
			'pages' => array(
				array('not_empty'),
				array('numeric')
			),
			'status' => array(
				array('in_array', array(':value', array_keys(Status::STATUSES_HV)))
			)
		);
	}

	public function labels() {
		return array(
			'pages' => 'Количество страниц',
			'catalog_id' => 'Раздел',
			'status' => 'Статус',
			'name' => 'Название'
		);
	}

	static public function get_description_label($material_id) {
		switch ($material_id) {
			case Model_Item::MATERIAL_LITERATURE:
				return 'Описание книги';
				break;
			case Model_Item::MATERIAL_ARTICLE:
				return 'Текст статьи';
				break;
			case Model_Item::MATERIAL_NEWS:
				return 'Текст новости';
				break;
		}
	}

	public function filters() {
		return array(
			'name' => array(
				array('HelperText::super_trim', array(':value')),
				array('strip_tags', array(':value'))
			)
		);
	}

	// ==========================================
	// КОНСТАНТЫ
	// ==========================================
	const IS_NEW_TIME = 60 * 60 * 24 * 7; // неделя
	const EDITABLE_TIME = 60 * 60 * 24; // сутки

	// ТИПЫ МАТЕРИАЛОВ
	const MATERIAL_ARTICLE = 2;
	const MATERIAL_LITERATURE = 1;
	const MATERIAL_NEWS = 89;

	// РАЗМЕРЫ ФОТОК
	const PHOTO_SIZES_COMMON = array(
		'xs' => array(
			'width' => 138,
			'height' => 138
		)
	);
	const PHOTO_SIZES_ARTICLE = array(
		'xs' => Model_Item::PHOTO_SIZES_COMMON['xs'],
		'sm' => array(
			'width' => 400,
			'height' => 400,
		)
	);
	const PHOTO_SIZES_LITERATURE = array(
		'xs' => Model_Item::PHOTO_SIZES_COMMON['xs'],
		'sm' => array(
			'width' => 240,
			'height' => 240,
		)
	);

	// ВАЛИДНЫЕ ЗНАЧЕНИЯ ПОЛЕЙ
	const MIN_NAME = 5;
	const MIN_DESCRIPTION_OF_ARTICLE = 1; // все равно в wysiwyg невозможно подсчитать количество символов, которые ввел пользователь
	const ENTERS_TO_PREVIEW = 2;
	const MAX_PHOTO_SIZE = 5 * MB;

	const MAX_BOOK_SIZE = 10 * MB;

	const PHOTO_EXTENSIONS = array(
		IMAGETYPE_JPEG,
		IMAGETYPE_GIF,
		IMAGETYPE_PNG
	);

	const BOOK_EXTENSIONS = array(
		'docx',
		'fb2',
		'pdf',
		'epub',
		'txt',
		'rtf'
	);

	// ПАПКИ И ПУТИ
	const FOLDER = 'materials/';
	const FOLDER_ARTICLES = 'articles/';
	const FOLDER_LITERATURE = 'literature/';
	const FOLDER_NEWS = 'news/';
	const FOLDER_BOOKS = 'books/';
	const PATH_FILES = DOCROOT . FOLDER_FILES . Model_Item::FOLDER;
	const PATH_BOOKS = Model_Item::PATH_FILES . Model_Item::FOLDER_LITERATURE . 'books/';
	const FILE_NO_PHOTO = 'no-photo.jpg';
	// ==========================================

	public function get_rating() {
		$query = 'SELECT item_id, sum(rate) AS rate
							FROM item_rating
							WHERE item_id = ' . $this->id . '
							GROUP BY item_id';
		$result = DB::query(Database::SELECT, $query)
			->execute()
			->as_array();
		$result = reset($result)['rate'];
		if ($result == null) {
			$result = 0;
		}
		return $result;
	}

	public function render_item_in_line() {
		return '<div>
					<a class="black custom-elems__link custom-elems__link_type_underline-solid" href="' . $this->get_url() . '">' . $this->get_full_name() . '</a>
				</div>';
	}

	public function get_full_name() {
		$catalog = ORM::factory('Catalog', $this->catalog_id);
		$full_name = $catalog->title;
		while ($catalog->parent_id != 0) {
			$catalog = ORM::factory('Catalog', $catalog->parent_id);
			$full_name = $catalog->title . ' - ' . $full_name;
		}
		$full_name = $full_name . ' - ' . $this->name;

		return $full_name;
	}

	public function get_url() {
		$catalog = ORM::factory('Catalog', $this->catalog_id);
		$url = '';
		while ($catalog->parent_id != 0) {
			$url = '/' . $catalog->alias . $url;
			$catalog = ORM::factory('Catalog', $catalog->parent_id);
		}
		$url = '/' . $catalog->alias . $url . '/' . $this->id;
		return $url;
	}

	public function get_photo_by_size($size, $material_folder, $without_default = false) {
		$path = static::get_images_path($material_folder) . Helper::get_size_folder($size) . '/';
		if ($this->photo != '') {
			return $path . $this->photo;
		} else {
			if (!$without_default) {
				return $path . static::FILE_NO_PHOTO;
			}
		}
	}

	public static function get_images_path($material_folder) {
		return static::PATH_FILES . $material_folder . FOLDER_IMAGES;
	}

	protected static function get_size_folder($size) {
		return $size['width'] . 'x' . $size['height'];
	}

	public function get_edit_url() {
		return '/' . ORM::factory('Catalog', $this->get_material_catalog())->alias . '/edit/' . $this->id;
	}

	public function get_book_path() {
		return DOCROOT . FOLDER_FILES . static::FOLDER . static::FOLDER_LITERATURE . static::FOLDER_BOOKS . $this->id . '/';
	}

	public function get_user() {
		return ORM::factory('User', $this->user_id);
	}

	public function generate_book_name($ext = null) {
		return 'book_' . $this->id . '.' . $ext;
	}

	/**
	 * Выводит все item'ы с условием по списку id. И не просто выводит, а раскладывает их по каталогам.
	 * @param $catalog - с какого каталога начинать (функция также идет по нижним каталогам - детям, внукам и тд.)
	 * @param $ids - условие - id которых есть в списке ids.
	 * @param bool $with_link - нужна ли ссылка на страницу выведенного item'a?
	 */
	static public function render_tree($catalog, $ids, $with_link = false, $is_admin) {
		$children = ORM::factory('Catalog')->where('parent_id', '=', $catalog->id)->find_all();
		$count_children = count($children);

		$items_in_catalog = ORM::factory('Item')
			->where('catalog_id', 'IN', $catalog->get_children(true))
			->where('id', 'IN', $ids)
			->find_all();

		if ((count($items_in_catalog)) && ($catalog->parent_id != 0)) {
			echo '<div class="custom-elems__point-6"><span>[' . $catalog->title . ']</span>';
			if ($count_children == 0) {
				$items = ORM::factory('Item')->where('catalog_id', '=', $catalog->id)->where('id', 'IN', $ids)->find_all();
				foreach ($items as $item) {
					$time = strtotime($item->date);
					$myFormatForView = date("d.m.y", $time);
					$tags = $item->get_tags($is_admin);
					foreach ($tags as $tag) {
						$tags_string .= ColorTags::render_tag($tag);
					}
					echo '<div class="custom-elems__point-16 item-tree"><a class="black" ' . ($with_link ? ' href="' . $item->get_url() . '"' : '') . '>';
					if ($item->get_material_catalog() == static::MATERIAL_LITERATURE) {
						$name = $item->name;
					} else {
						$name = $item->name;
					}
					echo $name . ' (' . $myFormatForView . ')</a><span class="item-tree__tags-line">' . $tags_string . '</span></div>';
				}
			}
			echo '</div>';
		}
		if ($count_children > 0) {
			foreach ($children as $child) {
				static::render_tree($child, $ids, $with_link, $is_admin);
			}
		}
	}

	private function get_material_catalog_inner($catalog) {
		if ($catalog->parent_id != 0) {
			$ctg = ORM::factory('Catalog', $catalog->parent_id);
			return $this->get_material_catalog_inner($ctg);
		} else {
			return $catalog;
		}
	}
	public function get_material_catalog() {
		return $this->get_material_catalog_inner(ORM::factory('Catalog', $this->catalog_id));
	}

	public function get_item_type() {
		$catalog = ORM::factory('Catalog', $this->catalog_id);
		return $catalog;
	}

	public function get_filters($num = NULL) {
		if (!$preload_filters) {
			$preload_filters = DB::select(array('filters.title', 'filter_title'), 'filter_values.*')->from('filter_values')
				->join('item_filters')->on('filter_values.id', '=', 'item_filters.filter_value_id')
				->join('filters')->on('filters.id', '=', 'filter_values.filter_id')
				->where('item_filters.item_id', '=', $this->id)
				->as_object()
				->execute()
				->as_array('filter_id');
		}

		if ($num === NULL) {
			return $preload_filters;
		} else {
			return $preload_filters[$num];
		}
	}

	public static function is_new_item($item) {
		return ((strtotime($item['date']) + static::IS_NEW_TIME) > time()) ? true : false;
	}

	public function is_new() {
		return ((strtotime($this->date) + static::IS_NEW_TIME) > time()) ? true : false;
	}

	public function get_tags($with_hidden = false) {
		$tags = [];
		$items_tags_ids = ORM::factory('ItemTag')->where('item_id', '=', $this->id)->find_all()->as_array(null, 'tag_id');
		if (count($items_tags_ids)) {
			$tags = ORM::factory('Tag')->where('id', 'in', $items_tags_ids);
			if (!$with_hidden) {
				$tags = $tags->where('status', '=', 1);
			}
			$tags = $tags->find_all()->as_array();
		}
		return $tags;
	}

	public function get_comments() {
		$comments = ORM::factory('ItemComment')->where('item_id', '=', $this->id)->find_all()->as_array();
		return $comments;
	}

	public function get_authors_string() {
		$items_authors = ORM::factory('ItemAuthor')->where('item_id', '=', $this->id)->find_all()->as_array();
		$authors = [];
		foreach ($items_authors as $item_author) {
			$author = ORM::factory('Author', $item_author->author_id);
			$authors[] = '<a href="' . $author->get_link_to_search() . '" class="black custom-elems__link custom-elems__link_type_underline-solid">' . $author->title . '</a>';
		}
		$result = implode(', ', $authors);
		return $result;
	}

	public function get_authors() {
		$items_authors = ORM::factory('ItemAuthor')->where('item_id', '=', $this->id)->find_all()->as_array();
		$authors = [];
		foreach ($items_authors as $item_author) {
			$author = ORM::factory('Author', $item_author->author_id);
			$authors[] = $author->title;
		}
		return implode(',', $authors);
	}

	// Доступ для просмотра Item'а
	public function have_access_to_hidden() {
		if (
			($this->status == Status::STATUS_VISIBLE_VALUE) ||
			($this->status == Status::STATUS_HIDDEN_VALUE && (CurrentUser::get_user()->is_admin() || CurrentUser::get_user()->is_item_author($this)))
		) {
			return true;
		} else {
			return false;
		}
	}

	public function get_books_from_database($detailed = false) {
		$files = [];
		if ($this->files) {
			foreach (explode(';', $this->files) as $i => $v) {
				// explode возвращает пустой элемент в случае отсутствия совпадения, а это нам не нужно. поэтому ставим if.
				if ($v) {
					if (!$detailed) {
						$files[$i] = $v;
					} else {
						$files[$i]['name'] = pathinfo($v, PATHINFO_FILENAME);
						$files[$i]['ext'] = pathinfo($v, PATHINFO_EXTENSION);
						$files[$i]['url'] = Model_Item::PATH_BOOKS . $this->id . '/' . $v;
					}
				}
			}
		}
		return $files;
	}

	public function get_books_from_directory() {
		$files = [];
		$path_to_files = static::PATH_BOOKS . $this->id . '/';
		$scanned_dir = array_diff(scandir($path_to_files), array('..', '.'));
		$i = 0;
		foreach ($scanned_dir as $filename) {
			$path_to_file = $path_to_files . $filename;
			$files[$i]['path'] = $path_to_file;
			$files[$i]['fullname'] = $filename;
			$files[$i]['size'] = filesize($path_to_file);
			$name_and_ext = Helper::split_name_and_ext($filename);
			$files[$i]['name'] = $name_and_ext['name'];
			$files[$i]['ext'] = $name_and_ext['ext'];
			$i++;
		}
	}


	public function validate_books($to_delete_string, $uploaded_books) {
		// ==============================================
		// Валидация
		// ==============================================
		// ----------------------------------------------
		// Получение расширений существующих файлов
		// ----------------------------------------------
		$exist_files = explode(';', $this->files);
		$exist_files = array_filter($exist_files);
		$exist_exts = [];
		foreach ($exist_files as $exist_file) {
			$exist_exts[] = Helper::get_extension($exist_file);
		}

		// ----------------------------------------------
		// Валидация наличия файлов у публикации (должен быть как минимум 1)
		// ----------------------------------------------
		$validation = new Validation(array());
		$count_files = count($exist_files) + count($uploaded_books);
		$to_delete_names = explode(';', $to_delete_string);
		$to_delete_names = array_filter($to_delete_names);
		if (
			// если у item'а нет файлов и файлы не загружаются
			(!$this->files && empty($uploaded_books)) ||

			// если у item'а есть файлы, пользователь их удаляет и количество удаленных >= количеству существующих / загружаемых
			(count($to_delete_names) >= $count_files)
		) {
			$validation->error('books', 'not_files');
			throw new ORM_Validation_Exception($this->errors_filename(), $validation);
		}

		// ----------------------------------------------
		// Валидация максимального размера
		// ----------------------------------------------
		foreach ($uploaded_books as $ub) {
			if ($ub['size'] > Model_Item::MAX_BOOK_SIZE) {
				$validation->error('books', 'exceeds_maximum_size', array('param1' => $ub['name']));
				throw new ORM_Validation_Exception($this->errors_filename(), $validation);
			}
		}

		// ----------------------------------------------
		// Валидация удаления
		// ----------------------------------------------
		// Валидация удаления одинаковых расширений
		$to_delete_exts = [];
		foreach ($to_delete_names as $name) {
			$to_delete_exts[] = Helper::get_extension($name);
		}
		$to_delete_exts_unique = array_unique($to_delete_exts);
		if (count($to_delete_exts) !== count($to_delete_exts_unique)) {
			$validation->error('books', 'delete_same');
			throw new ORM_Validation_Exception($this->errors_filename(), $validation);
		}

		// Валидация удаления несуществующих файлов
		foreach ($to_delete_exts as $ext) {
			if (!in_array($ext, $exist_exts)) {
				$validation->error('books', 'delete_non_exist');
				throw new ORM_Validation_Exception($this->errors_filename(), $validation);
			}
		}

		// ----------------------------------------------
		// Валидация загрузки
		// ----------------------------------------------
		// Валидация загрузки одинаковых расширений
		$to_upload_exts = [];
		foreach ($uploaded_books as $ub) {
			$to_upload_exts[] = strtolower(Helper::get_extension($ub['name']));
		}
		$to_upload_exts_unique = array_unique($to_upload_exts);
		if (count($to_upload_exts) !== count($to_upload_exts_unique)) {
			$validation->error('books', 'upload_same');
			throw new ORM_Validation_Exception($this->errors_filename(), $validation);
		}

		// Валидация загрузки неподходящих расширений
		foreach ($to_upload_exts as $ext) {
			if (!in_array($ext, Model_Item::BOOK_EXTENSIONS)) {
				$validation->error('books', 'incorrect_ext', array('param1' => $ext));
				throw new ORM_Validation_Exception($this->errors_filename(), $validation);
			}
		}

		// ----------------------------------------------
		// Проверка, что происходит загрузка файлов, которые не существуют. То есть, которых нет в files или которые там есть, но также есть и в массиве на удаление
		// ----------------------------------------------
		foreach ($to_upload_exts as $ext) {
			if (in_array($ext, $exist_exts) && !in_array($ext, $to_delete_exts)) {
				$validation->error('books', 'same_ext');
				throw new ORM_Validation_Exception($this->errors_filename(), $validation);
			}
		}
	}

	public function delete_and_save_books($to_delete_string, $uploaded_books) {
		$exist_files = explode(';', $this->files);
		$exist_files = array_filter($exist_files);

		// -------------------------------
		// Удаление книг
		// -------------------------------
		$to_delete_names = explode(';', $to_delete_string);
		$to_delete_names = array_filter($to_delete_names);

		if (mb_strlen($to_delete_string)) {
			$path = $this->get_book_path();
			foreach ($to_delete_names as $to_delete_name) {
				$target = $path . $to_delete_name;
				if (file_exists($target)) {
					unlink($target);
				}

				foreach ($exist_files as $key => $file) {
					if ($file === $to_delete_name) {
						unset($exist_files[$key]);
					}
				}
				if (count($exist_files)) {
					$item_files = implode(';', $exist_files);
				} else {
					$item_files = '';
				}

				$this->files = $item_files;
			}
		}

		// -------------------------------
		// Сохранение книг
		// -------------------------------
		$path_to_files = static::PATH_BOOKS . $this->id . '/';
		if (!is_dir($path_to_files)) {
			mkdir($path_to_files, 0777, true);
		}

		if ($this->files) {
			$item_files = explode(';', $this->files);
		} else {
			$item_files = [];
		}

		foreach ($uploaded_books as $book) {
			$name_and_ext = Helper::split_name_and_ext($book['name']);
			$ext = $name_and_ext['ext'];
			$book_name = $this->generate_book_name($ext);
			$item_files[] = $book_name;
			$target = $path_to_files . $book_name;
			move_uploaded_file($book['tmp_name'], $target);
		}

		$this->files = implode(';', $item_files);
		$this->save();
	}

}