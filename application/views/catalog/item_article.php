<?php
$comments = count(ORM::factory('ItemComment')->where('item_id', '=', $item->id)->where('status', '=', 1)->find_all());
$date = date("d.m.y", strtotime($item->date));
$tags = $item->get_tags($user->is_admin());
$author = ORM::factory('User', $item->user_id);
$can_edit = $user->can_edit_item($item);
$material_url = '/' . $material->alias;
?>

<div data-material-type-url="/articles" class="page-item pds-bottom">

	<?= Widget_Breadcrumbs::render($breadcrumbs); ?>

	<div class="board-article board-item board-item_paddings">
		<div class="board-article__top-line clearfix">
			<div class="board-article__title-wrap">
				<h1 class="board-article__title">
					<span class="board-article__name"><?= $item->name ?></span>
				</h1>
			</div>
			<?= Widget_ItemStats::render($item, array('views', 'date')) ?>
		</div>
		<div class="board-article__basic-info clearfix">
			<?php if ($photo) { ?>
				<div class="board-article__img-wrap">
					<img class="board-article__img" alt src="<?= $photo ?>"/>
				</div>
			<?php } ?>
			<div class="board-article__description">
				<?= $item->description ?>
			</div>
		</div>

		<div class="page-item__share">
			<?=
			Widget_Share::render(array(
				'url' => $item->get_url()
			))
			?>
		</div>

		<div>
			<span>Добавил:</span>
			<a href="<?= $author->get_url() ?>" class="board-article__link black custom-elems__link custom-elems__link_type_underline-solid"><?= $author->username ?></a>
		</div>
		<?php if ($user->is_admin_or_owner($item) !== Model_User::USER_IS_NOT_ADMIN_OR_OWNER) { ?>
			<div>
				<span>Статус:</span>
				<span><?= Status::STATUSES_HV[$item->status] ?></span>
			</div>
		<?php } ?>
		<div class="board-article__item-params">
			<?= Widget_ItemParams::render($item); ?>
		</div>

		<?php if (count($tags)) {
			echo '<div class="board-item__color-tags"' . ($can_edit ? ' style="padding-bottom: 6px;"' : '') . '>';
			echo ColorTags::render_bar($tags, false);
			echo '</div>';
		} ?>
		<?php
		if ($can_edit) { ?>
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