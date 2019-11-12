<div class="page-index<?= $seo_data['content'] ? '' : ' pds-bottom' ?>">

	<div class="billboard">
		<div class="billboard__slider">
			<div class="billboard__slide billboard__slide_is-main">
				<div class="billboard__slide-header">
					<div class="billboard__header-shadow"></div>
					Добро пожаловать на Turznanie.ru!
				</div>
				<div class="billboard__slide-body">
					<img alt class="billboard__image" src="<?= $billboard_main_img ?>">
				</div>
			</div>
			<div class="billboard__slide">
				<div class="billboard__slide-header">
					<div class="billboard__header-shadow"></div>
					Самые популярные теги
				</div>
				<div class="billboard__slide-body">
					<div class="billboard__tags-cloud" data-tags='<?= $tags_cloud ?>'></div>
				</div>
			</div>
			<div class="billboard__slide">
				<div class="billboard__slide-header">
					<div class="billboard__header-shadow"></div>
					Наши друзья
				</div>
				<div class="billboard__slide-body">
					<img alt class="billboard__image" src="assets/images/billboard_1.jpg">
				</div>
			</div>
		</div>
	</div>

	<?php if (count($items[Model_Item::MATERIAL_NEWS])) { ?>
		<div>
			<h2 class="header-separator">Недавние новости</h2>
			<div>
				<?php
				foreach ($items[Model_Item::MATERIAL_NEWS] as $item) {
					Catalog::render_item_material($item, $is_admin, Model_Item::MATERIAL_NEWS, false, false);
				}
				?>
			</div>
		</div>
	<? } ?>

	<?php if (count($items[Model_Item::MATERIAL_ARTICLE])) { ?>
		<div>
			<h2 class="header-separator">Недавние статьи</h2>
			<div>
				<?php
				foreach ($items[Model_Item::MATERIAL_ARTICLE] as $item) {
					Catalog::render_item_material($item, $is_admin, Model_Item::MATERIAL_ARTICLE, false, false);
				}
				?>
			</div>
		</div>
	<?php } ?>

	<?php if (count($items[Model_Item::MATERIAL_LITERATURE])) { ?>
		<div>
			<h2 class="header-separator">Недавняя литература</h2>
			<div>
				<?php
				foreach ($items[Model_Item::MATERIAL_LITERATURE] as $item) {
					Catalog::render_item_material($item, $is_admin, Model_Item::MATERIAL_LITERATURE, false, false);
				}
				?>
			</div>
		</div>
	<?php } ?>

	<?php if ($seo_data['content']) { ?>
		<div class="index-content">
			<div class="about js-view-more" data-height="106">
				<div class="about__text-wrap js-view-more__tw">
					<div class="about__h1">
						<?= $seo_data['h1'] ?>
					</div>
					<div class="about__text">
						<?= $seo_data['content'] ?>
					</div>
					<div class="about__overlay js-view-more__overlay"></div>
				</div>
				<div class="about__btn-wrap">
					<a data-text="Скрыть" class="black custom-elems__link custom-elems__link_type_underline-solid about__btn js-view-more__btn">Читать далее</a>
				</div>
			</div>
		</div>
	<?php } ?>

</div>
