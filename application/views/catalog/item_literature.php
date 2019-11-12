<?php
$comments = count(ORM::factory('ItemComment')->where('item_id', '=', $item->id)->where('status', '=', 1)->find_all());
$date = date("d.m.y", strtotime($item->date));
$tags = $item->get_tags($user->is_admin());
$books = $item->get_books_from_database(true);
$material_url = '/' . $material->alias;
?>

<div data-material-type-url="/literature" class="page-item pds-bottom">

	<?= Widget_Breadcrumbs::render($breadcrumbs) ?>

	<div class="board-item board-item_paddings board-literature">
		<div class="board-literature__header-mobile"></div>
		<div class="board-literature__top-part clearfix">
			<div class="board-literature__leftside">
				<div class="board-literature__img-wrap">
					<img alt src="<?= $photo ?>"/>
				</div>
			</div>
			<div class="board-literature__rightside">
				<div class="board-literature__header"><?= $seo_data['title'] ?></div>
				<div class="board-literature__item-stats">
					<?= Widget_ItemStats::render($item, array('views', 'date')) ?>
				</div>
				<div class="board-literature__info-wrap">
					<div class="board-literature__info-left">
						<div class="board-literature__info-line">
							<span class="board-literature__info-key">Автор(ы)</span>
							<span class="board-literature__info-value"><?= $item->get_authors_string() ?></span>
						</div>
						<div class="board-literature__info-line">
							<span class="board-literature__info-key">Название</span>
							<span class="board-literature__info-value"><?= $item->name ?></span>
						</div>
						<div class="board-literature__info-line">
							<span class="board-literature__info-key">Страниц</span>
							<span class="board-literature__info-value"><?= $item->pages ?></span>
						</div>
						<?php
						if ($books_amount = count($books)) { ?>
							<div class="board-literature__info-line">
								<span class="board-literature__info-key">Файлы</span>
								<span class="board-literature__info-value">
									<?php
									$i = 0;
									foreach ($books as $book) {
										$i++;
										echo '<a download class="black custom-elems__link custom-elems__link_type_underline-solid js-file-uploader__upload-btn" href="' . Helper::const_to_client($book['url']) . '">' . $book['ext'] . '</a>' . ($i < $books_amount ? ' ' : '');
									}
									?>
								</span>
							</div>
						<?php } ?>
						<?php if ($user->is_admin()) { ?>
							<div class="board-literature__info-line">
								<span class="board-literature__info-key">Статус</span>
								<span class="board-literature__info-value"><?= Status::STATUSES_HV[$item->status] ?></span>
							</div>
						<?php } ?>
						<div class="board-literature__info-line">
							<?= Widget_ItemParams::render($item) ?>
						</div>
						<?php if (count($tags) > 0) { ?>
							<div class="board-literature__info-line">
								<?= ColorTags::render_bar($tags, false) ?>
							</div>
						<?php } ?>
					</div>
					<div class="board-literature__info-right">
					</div>
				</div>
			</div>
		</div>
		<div class="board-literature__bot-part">
			<div class="board-literature__description-wrap">
				<h2 class="custom-elems__h2">Описание</h2>
				<div class="board-literature__description"><?= $item->description ?></div>
			</div>

			<div class="page-item__share">
				<?=
				Widget_Share::render(array(
					'url' => $item->get_url()
				))
				?>
			</div>

			<?php if ($user->is_admin()) { ?>
				<div>
					<a class="page-item__change black custom-elems__link custom-elems__link_type_underline-solid" href="<?= $item->get_edit_url(); ?>">Изменить</a>
					<a data-id="<?= $item->id ?>" class="page-item__delete black custom-elems__link custom-elems__link_type_underline-solid">Удалить</a>
				</div>
			<?php } ?>
			<div class="board-item__comments-wrap">
				<?= Widget_Comments::render_comments($item, $material_url) ?>
			</div>
		</div>
	</div>

</div>