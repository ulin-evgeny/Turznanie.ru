<?php if (!Request::initial()->is_ajax()) { ?>
	<div id="middle" class="page-catalog<?= $pagination->get_pages() < 2 ? ' pds-bottom' : '' ?> clearfix<?= ($catalog_type == Catalog::CATALOG_TYPE_ADMIN ? ' page-catalog_type_admin' : ' page-catalog_type_common') ?>">
<?php } ?>

	<div class="clearfix">
		<div class="right-side">
			<?= Widget_Breadcrumbs::render($breadcrumbs) ?>
		</div>
	</div>

	<div class="left-side page-catalog__left-side">
		<div class="header-separator header-separator_pos_left"><?= $seo_data['h1'] ?></div>
		<div class="left-side__body">
			<?php
			// Меню слева
			switch ($catalog_type) {
				case Catalog::CATALOG_TYPE_ADMIN:
					echo Widget_CatalogMenu::render_menu_admin(ORM::factory('Seo')->where('parent_id', '=', Model_Seo::ID_ADMIN_PANEL)->find_all()->as_array());
					break;
				case Catalog::CATALOG_TYPE_NORMAL:
					echo Widget_CatalogMenu::render_menu($material->id, $last_catalog->alias);
					break;
			}

			// Фильтры
			$with_filters = false;
			if ($catalog_type === Catalog::CATALOG_TYPE_NORMAL) {
				echo $with_filters = Widget_CatalogFilters::render_catalog_filters($get_params, intval($material->id));
			}
			?>
		</div>
	</div>

	<div class="page-catalog__right-side right-side">

		<div class="screen-pc header-separator header-separator_pos_right clearfix <?= $show_add_btn ? '' : 'page-catalog__hide-on-less-lg' ?>">
			<?php if ($show_add_btn) { ?>
				<a class="right page-catalog__btn-add button button_rounded"<?= isset($btn_add_href) ? ' href="' . $btn_add_href . '"' : '' ?>>Добавить</a>
			<?php } ?>
		</div>

		<div class="choosing-panel screen-mobile">
			<?php if ($with_filters) {
				echo '<a class="choosing-panel__btn choosing-panel__btn_with_icon choosing-panel__btn_type_filter button button_rounded">Фильтры</a>';
			} ?>
			<?php if ($show_add_btn) { ?>
				<a class="page-catalog__btn-add choosing-panel__btn button button_rounded"<?= isset($btn_add_href) ? ' href="' . $btn_add_href . '"' : '' ?>>Добавить</a>
			<?php } ?>
		</div>

		<?php if (isset($get_params['tag'])) {
			function fpanel_btn($url, $title) {
				return '<a class="fpanel__btn" href="' . $url . '" title="Удалить фильтр">' . $title . '<span class="icon-cancel fpanel__icon"></span></a> ';
			} ?>
			<div class="fpanel line-panel">
				<span>Выбранные фильтры:</span>
				<?php
				if ($get_params['tag']) {
					$url = URL::query(array('tag' => null));
					$title = 'Тег: ' . $get_params['tag'];
					echo fpanel_btn($url, $title);
				}
				?>
			</div>
		<?php } ?>

		<div class="sorter line-panel clearfix">
			<div class="sorter__on-page">
				<span class="sorter__on-page-text">Показывать по:</span>
				<select data-value="<?= $pagination->get_on_page() ?>" class="on-page page-catalog__nice-select nice-select-style-1">
					<?php
					$on_page_array = $pagination->get_on_page_array();
					foreach ($on_page_array as $v) {
						$url_query_params = array('page' => null);
						$url_query_params['on_page'] = ($v == $on_page_array[0] ? null : $v);
						$href = URL::query($url_query_params);
						?>
						<option data-href="<?= $href ?>"><?= $v ?></option>
					<?php } ?>
				</select>
			</div>

			<span class="sorter__text">Сортировать по:</span>
			<?php
			foreach ($sort->get_sort_by_btns() as $btn) {
				foreach ($btn as $k => $v) {
					$result = array();
					$result['page'] = null;

					// sort_way
					// для переключения sort_way нет кнопки. переключается он по нажатию на активную кнопку sort_by
					$sort_way_default = $sort->get_sort_way_default();
					if ($sort->get_sort_by() == $k) {
						$active_btn = true;
						if ($sort->get_sort_way() != $sort_way_default) {
							$result += array('sort_way' => null);
							$sort_way_class = 'sorter__btn_to-' . $sort_way_default;
						} else {
							$sort_way_another = $sort->get_sort_way_another();
							$result += array('sort_way' => $sort_way_another);
							$sort_way_class = 'sorter__btn_to-' . $sort_way_another;
						}
					} else {
						$active_btn = false;
						$result += array('sort_way' => null);
						$sort_way_class = 'sorter__btn_to-' . $sort_way_default;
					}

					// sort_by
					if ($k == $sort->get_sort_by_default()) {
						$result += array('sort_by' => null);
					} else {
						$result += array('sort_by' => $k);
					}
					$href = URL::query($result);
					echo '<a class="sorter__btn' . ($active_btn ? ' active ' : ' ') . $sort_way_class . '" href="' . $href . '">' . $v . '</a>';
				}
			}
			?>
		</div>

		<?php
		echo '<div class="clearfix">';
		switch ($catalog_type) {
			case Catalog::CATALOG_TYPE_NORMAL:
				foreach ($items as $item) {
					echo Catalog::render_item_material($item, $render_statusbar, $material->id, $user->is_admin());
				}
				break;
			case Catalog::CATALOG_TYPE_ADMIN:
				$id = $seo_page->id;
				switch (true) {
					case (in_array($id, array(Model_Seo::ID_CATALOG_TAGS, Model_Seo::ID_CATALOG_AUTHORS))):
						echo Catalog::render_item_admin($seo_page); // для динамического добавления
						foreach ($items as $item) {
							echo Catalog::render_item_admin($seo_page, $item);
						}
						break;
					case ($id == Model_Seo::ID_CATALOG_USERS):
						foreach ($items as $item) {
							echo Catalog::render_item_user($item);
						}
						break;
					case ($id == Model_Seo::ID_CATALOG_SEO):
						foreach ($items as $item) {
							echo Catalog::render_item_seo($item);
						}
						break;
					case ($id == Model_Seo::ID_CATALOG_COMMENTS):
						foreach ($items as $item) {
							echo Catalog::render_item_comment($item);
						}
						break;
				}
				break;
		}
		echo '</div>';

		echo $pagination->render(1);
		?>

	</div>

<?php if ($catalog_type === Catalog::CATALOG_TYPE_NORMAL) { ?>
	<input type="hidden" class="js-server-values"
		<?php if ($material->id == Model_Item::MATERIAL_LITERATURE) { ?>
			data-min-pages="<?= $get_params['min_pages'] ?>"
			data-max-pages="<?= $get_params['max_pages'] ?>"
			data-pages-from="<?= $get_params['pages_from'] ?>"
			data-pages-to="<?= $get_params['pages_to'] ?>"
		<?php } elseif ($material->id == Model_Item::MATERIAL_ARTICLE || $material->id == Model_Item::MATERIAL_NEWS) { ?>
			data-min-date="<?= $get_params['min_date'] ?>"
			data-max-date="<?= $get_params['max_date'] ?>"
			data-date-from="<?= $get_params['date_from'] ?>"
			data-date-to="<?= $get_params['date_to'] ?>"
		<?php } ?>
	/>
<?php } ?>

<?php if (!Request::initial()->is_ajax()) { ?>
	</div>
<?php } ?>