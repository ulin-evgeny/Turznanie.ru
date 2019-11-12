<?php

class Catalog {
	// =============================================
	// Константы
	// =============================================
	// Литература, Статьи, Новости
	const CATALOG_TYPE_NORMAL = 1;
	// Админские разделы
	const CATALOG_TYPE_ADMIN = 2;

	// =============================================
	// Функции
	// =============================================
	static public function get_status_class($item) {
		if ($item->status == 0) {
			return 'item-status-bar_color_hidden';
		} elseif ($item->status == 1) {
			return 'item-status-bar_color_shown';
		}
	}

	static public function get_material_type_catalog_by_alias($alias) {
		$alias_literature = ORM::factory('Catalog', Model_Item::MATERIAL_LITERATURE)->alias;
		$alias_article = ORM::factory('Catalog', Model_Item::MATERIAL_ARTICLE)->alias;
		$alias_news = ORM::factory('Catalog', Model_Item::MATERIAL_NEWS)->alias;

		switch ($alias) {
			case $alias_literature:
				$id = Model_Item::MATERIAL_LITERATURE;
				break;
			case $alias_article:
				$id = Model_Item::MATERIAL_ARTICLE;
				break;
			case $alias_news:
				$id = Model_Item::MATERIAL_NEWS;
				break;
		}
		return ORM::factory('Catalog', $id);
	}

	/**
	 * Возвращает параметры, необходимые для пагинации и фильтров (Например, количество item'ов, самая минимальная цена. Не на странице, а вообще).
	 * @param $catalog_ids
	 * @param $get_params
	 * @return mixed
	 */
	static public function get_items_params($catalog_ids, $get_params) {
		// ------------------------------------
		// Получение минимальных и максимальных значений - это нужно для фильтрации
		// ------------------------------------
		$query = 'SELECT count(*) AS count, MAX(date) AS max_date, MIN(date) AS min_date, MAX(pages) AS max_pages, MIN(pages) AS min_pages
							FROM items
							WHERE catalog_id IN :catalog_ids';

		if (!CurrentUser::get_user()->is_admin()) {
			$query .= ' AND status = ' . Status::STATUS_VISIBLE_VALUE;
		}

		// Применение фильтрации по тегу
		$item_ids_with_tag = array();
		if (isset($get_params['tag'])) {
			$tag = ORM::factory('Tag')->where('title', '=', $get_params['tag']);
			if (!CurrentUser::get_user()->is_admin()) {
				$tag = $tag->where('status', '=', Status::STATUS_VISIBLE_VALUE);
			}
			$tag->find();
			if ($tag->loaded()) {
				$item_ids_with_tag = ORM::factory('ItemTag')->where('tag_id', '=', $tag->id)->find_all()->as_array(null, 'item_id');
			} else {
				$item_ids_with_tag = array(-1);
			}
			$query .= ' AND id in :item_ids_with_tag';
		}

		$absolute_values = DB::query(Database::SELECT, $query)
			->param(':catalog_ids', $catalog_ids)
			->param(':item_ids_with_tag', $item_ids_with_tag)
			->execute()
			->as_array()[0];

		// ------------------------------------
		// Получение current_count - это нужно для пагинации. Это противоположное значение максимальному, это значение с учетом примененных фильтров.
		// ------------------------------------
		$query = 'SELECT count(*) AS current_count
							FROM items
							WHERE catalog_id IN :catalog_ids';

		if (!CurrentUser::get_user()->is_admin()) {
			$query .= ' AND status = ' . Status::STATUS_VISIBLE_VALUE;
		}

		// Применение фильтрации по тегу
		if (isset($get_params['tag'])) {
			$query .= ' AND id in :item_ids_with_tag';
		}

		// Применение фильтрации по страницам и дате
		if (isset($get_params['pages_from']) && isset($get_params['pages_to'])) {
			$query .= ' AND pages >= ' . $get_params['pages_from'] . ' AND pages <= ' . $get_params['pages_to'];
		}
		if (isset($get_params['date_from']) && isset($get_params['date_to'])) {
			$query .= ' AND date >= "' . date('Y-m-d H:i:s', $get_params['date_from']) . '" AND date <= "' . date('Y-m-d H:i:s', $get_params['date_to']) . '"';
		}

		$current_values = DB::query(Database::SELECT, $query)
			->param(':catalog_ids', $catalog_ids)
			->param(':item_ids_with_tag', $item_ids_with_tag)
			->execute()
			->as_array()[0];

		$absolute_values['max_date'] = strtotime($absolute_values['max_date']);
		$absolute_values['min_date'] = strtotime($absolute_values['min_date']);

		$result = array_merge($absolute_values, $current_values);

		return $result;
	}

	static public function get_items_and_params($catalog_ids, Sort $sort, Pagination $pagination, $get_params) {
		$query = 'SELECT i.*, COALESCE(SUM(ir.rate), 0) rating
							FROM items i
							LEFT JOIN item_rating ir ON ir.item_id = i.id
							WHERE i.catalog_id IN :catalog_ids';

		if (!CurrentUser::get_user()->is_admin()) {
			$query .= ' AND i.status = ' . Status::STATUS_VISIBLE_VALUE;
		}

		// Применение фильтрации по тегу
		$item_ids_with_tag = array();
		if (isset($get_params['tag'])) {
			$tag = ORM::factory('Tag')->where('title', '=', $get_params['tag']);
			if (!CurrentUser::get_user()->is_admin()) {
				$tag = $tag->where('status', '=', Status::STATUS_VISIBLE_VALUE);
			}
			$tag->find();
			if ($tag->loaded()) {
				$item_ids_with_tag = ORM::factory('ItemTag')->where('tag_id', '=', $tag->id)->find_all()->as_array(null, 'item_id');
			} else {
				$item_ids_with_tag = array(-1);
			}
			$query .= ' AND i.id in :item_ids_with_tag';
		}

		// Применение фильтрации по страницам и дате и установка параметров, если фильтрация не применялась
		if (isset($get_params['pages_from']) && isset($get_params['pages_to'])) {
			$query .= ' AND i.pages >= ' . $get_params['pages_from'] . ' AND i.pages <= ' . $get_params['pages_to'];
		} else {
			$params['pages_from'] = $get_params['min_pages'];
			$params['pages_to'] = $get_params['max_pages'];
		}
		if (isset($get_params['date_from']) && isset($get_params['date_to'])) {
			$query .= ' AND i.date >= "' . date('Y-m-d H:i:s', $get_params['date_from']) . '" AND i.date <= "' . date('Y-m-d H:i:s', $get_params['date_to']) . '"';
		} else {
			$params['date_from'] = $get_params['min_date'];
			$params['date_to'] = $get_params['max_date'];
		}

		$query .= ' GROUP BY i.id ORDER BY ' . $sort->get_sort_by() . ' ' . $sort->get_sort_way();
		$query .= ' LIMIT ' . $pagination->get_on_page() . ' OFFSET ' . $pagination->get_offset();

		$items = DB::query(Database::SELECT, $query)
			->param(':catalog_ids', $catalog_ids)
			->param(':item_ids_with_tag', $item_ids_with_tag)
			->execute()
			->as_array();

		return array(
			'items' => $items,
			'params' => $params
		);
	}

	static public function selection_and_sorting_orm_items($items, Sort $sort, Pagination $pagination) {
		$on_page = $pagination->get_on_page();
		$offset = ($pagination->get_page() - 1) * $on_page;
		$items->offset($offset)
			->limit($on_page)
			->order_by($sort->get_sort_by(), $sort->get_sort_way());
		return $items;
	}

	static public function render_item_admin($seo_page, $item = null) {
		$is_template = false;
		if (!$item) {
			$is_template = true;
		}
		$url = Model_Seo::get_alias_by_id(Model_Seo::ID_ADMIN_PANEL) . '/' . $seo_page->alias;
		$result = '';

		if ($is_template) {
			$result .= '<div class="item-admin-template item-admin-wrap" style="display: none;">';
		}

		$result .= '<div class="item-admin clearfix js-item' . ($is_template ? ' js-item_is-template' : '') . '" data-url="' . $url . '">';

		if ($is_template) {
			switch ($seo_page->id) {
				case Model_Seo::ID_CATALOG_AUTHORS:
					$item = ORM::factory('Author');
					break;
				case Model_Seo::ID_CATALOG_TAGS:
					$item = ORM::factory('Tag');
					break;
			}
		}

		$result .= '<div class="item-admin__first-part">';

		if ($item) {
			$result .= '<input name="id" type="hidden" class="js-id" value="' . $item->id . '">';
		}
		$result .= '<input name="title" class="item-admin__title" value="' . $item->title . '">';

		if (!$is_template) {
			$result .= '<a data-url="' . $url . '/change_title' . '" class="item-admin__change-title black">Изменить</a>';
		}

		$result .= '</div><div class="item-admin__second-part">';

		if (!$is_template) {
			$result .= '<a title="Удалить" class="item-admin__delete item-admin__btn icon-cancel"></a>';
		} else {
			$result .= '<a title="Создать" class="item-admin__create item-admin__btn icon-ok"></a>';
		}

		// Статус
		$result .= '<span class="item-admin__change-status-wrap"><select class="' . (!$is_template ? 'js-item__ajax-select ' : '') . 'item-admin__change-status page-catalog__nice-select nice-select-style-1">';
		foreach (Status::STATUSES_HV as $value => $title) {
			$result .= '<option value="' . $value . '" ' . ($value == $item->status ? ' selected' : '') . '>' . $title . '</option>';
		}
		$result .= '</select></span>';
		// ------------

		$result .= '</div></div>';

		if ($is_template) {
			$result .= '</div>';
		}

		return $result;
	}

	static public function render_item_user($user) {
		echo '<div class="item-admin"><a class="black" href="' . $user->get_url() . '">' . $user->username . '</a></div>';
	}

	static public function render_item_comment($comment) {
		$item = ORM::factory('Item', $comment->item_id);
		$user = ORM::factory('User', $comment->user_id);

		$result = '<div class="item-admin item-comment">
								<a class="black custom-elems__link custom-elems__link_type_underline-solid" href="' . $comment->get_url() . '">' . $item->get_full_name() . '</a>
								<div>Автор: ' . $user->username . '</div>
								<div>' . $comment->preview . '</div>
								<div class="item-status-bar ' . Catalog::get_status_class($comment) . '"></div>
							 </div>';

		return $result;
	}

	static public function render_item_seo($page) {
		$url_to_change = Model_Seo::get_alias_by_id(Model_Seo::ID_ADMIN_PANEL) . Model_Seo::get_alias_by_id(Model_Seo::ID_CATALOG_SEO) . '/' . $page->id;
		$url = $page->get_url();
		$result = '<div class="item-admin item-seo">
								<div class="item-seo__title">' . $page->title . '</div>
									<div class="item-seo__url">
										<a' . (Model_Seo::url_get_params($url) ? '' : ' href="' . $url . '"') . ' class="item-seo__btn">' . Helper::get_site_url() . $url . '</a>
									</div>
									<div class="item-seo__btns">
										<a href="' . $url_to_change . '" class="item-seo__btn custom-elems__link custom-elems__link_type_underline-solid black">Редактировать</a>
									</div>
								</div>';
		echo $result;
	}

	static public function render_item_material($item, $render_statusbar, $material, $is_admin, $tags_with_links = true) {
		$item_orm = ORM::factory('Item', $item['id']);
		$item_type = $item_orm->get_item_type()->title;
		if ($material == Model_Item::MATERIAL_ARTICLE || $material == Model_Item::MATERIAL_NEWS) {
			$item_author = ORM::factory('User', $item_orm->user_id);
			$item_author_url = $item_author->get_url();
			$item_author = $item_author->username;
			$material_folder = Model_Item::FOLDER_ARTICLES;
		} elseif ($material == Model_Item::MATERIAL_LITERATURE) {
			$material_folder = Model_Item::FOLDER_LITERATURE;
		}

		$tags = $item_orm->get_tags($is_admin);
		$photo = Helper::const_to_client($item_orm->get_photo_by_size(Model_Item::PHOTO_SIZES_ARTICLE['xs'], $material_folder));

		// ----------------------------
		// Статья
		// ----------------------------
		if ($material == Model_Item::MATERIAL_ARTICLE || $material == Model_Item::MATERIAL_NEWS) { ?>
			<div class="item-material">
				<div class="item-material__content-wrap">
					<div class="item-material__content clearfix">
						<div class="item-material__image left">
							<img src="<?= $photo ?>" alt/>
						</div>
						<div class="item-material__body">
							<div class="item-material__top-line item-material__top-line_article">
								<div class="clearfix">
									<?= Widget_ItemStats::render($item_orm) ?>
									<div class="item-material__title-wrap">
										<a class="item-material__title" href="<?= $item_orm->get_url() ?>">
											<span class="item-material__sm-block"><?= $item_orm->name ?></span>
										</a>
									</div>
								</div>
								<div>Добавил: <a class="black custom-elems__link custom-elems__link_type_underline-solid" href="<?= $item_author_url ?>"><?= $item_author ?></a></div>
								<div>Категория: <?= $item_type ?></div>
							</div>
							<div class="item-material__description">
								<?= $item_orm->preview ?>
							</div>
							<?php if (count($tags) > 0) { ?>
								<div class="item-material__color-tags-wrap">
									<?= ColorTags::render_bar($tags, $tags_with_links) ?>
								</div>
							<?php } ?>
						</div>
					</div>
					<?php
					if ($render_statusbar) {
						echo '<div class="item-material__item-status-bar item-status-bar ' . Catalog::get_status_class($item_orm) . '"></div>';
					}
					?>
				</div>
			</div>
		<?php }

		// ----------------------------
		// Литература
		// ----------------------------
		if ($material == Model_Item::MATERIAL_LITERATURE) { ?>
			<div class="item-material">
				<div class="item-material__content-wrap">
					<div class="item-material__content clearfix">
						<div class="item-material__image left">
							<img src="<?= $photo ?>" alt/>
						</div>
						<div class="item-material__body">
							<div class="item-material__top-line item-material__top-line_literature">
								<div class="clearfix">
									<?= Widget_ItemStats::render($item_orm) ?>
									<div class="item-material__title-wrap">
										<a class="item-material__title" href="<?= $item_orm->get_url() ?>">
											<span class="item-material__name item-material__sm-block"><?= $item_orm->name ?></span>
										</a>
									</div>
								</div>
								<div>Автор(ы): <?= $item_orm->get_authors_string() ?></div>
								<div>Категория: <?= $item_type ?></div>
								<div>Страниц: <?= $item_orm->pages ?></div>
							</div>
							<?php if (count($tags) > 0) { ?>
								<div class="item-material__color-tags-wrap">
									<?= ColorTags::render_bar($tags, $tags_with_links) ?>
								</div>
							<?php } ?>
						</div>
					</div>
					<?php
					if ($render_statusbar) {
						echo '<div class="item-material__item-status-bar item-status-bar ' . Catalog::get_status_class($item_orm) . '"></div>';
					}
					?>
				</div>
			</div>
		<?php }
	}

}