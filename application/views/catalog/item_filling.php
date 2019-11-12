<?php
$page_selector = 'page-item-filling_material-type_';
switch ($material->id) {
	case Model_Item::MATERIAL_LITERATURE:
		$page_selector .= 'literature';
		break;
	case Model_Item::MATERIAL_ARTICLE:
		$page_selector .= 'articles';
		break;
	case Model_Item::MATERIAL_NEWS:
		$page_selector .= 'news';
		break;
}

if ($material->id == Model_Item::MATERIAL_LITERATURE) {
	$image_header = 'Обложка';
	$image_text = 'Обложка используется для превью в списке книг, а также (если вы загрузили ее) будет отображаться на странице самой книги.';
	$photo_sizes = Model_Item::PHOTO_SIZES_LITERATURE;
	$folder = Model_Item::FOLDER_LITERATURE;
	$books = $filling_item->get_books_from_database(true);
	$exist_exts = array_column($books, 'ext');
	$authors_string = $filling_item->get_authors();
} elseif ($material->id == Model_Item::MATERIAL_ARTICLE || $material->id == Model_Item::MATERIAL_NEWS) {
	$image_header = 'Изображение';
	$image_text = 'Изображение используется для превью в списке статей, а также (если вы загрузили его) будет отображаться на странице самой статьи.';
	$photo_sizes = Model_Item::PHOTO_SIZES_ARTICLE;
	$folder = Model_Item::FOLDER_ARTICLES;
	$preview_maximum_length = $filling_item->table_columns()['preview']['character_maximum_length'];
}
$description_header = Model_Item::get_description_label($material->id);
$photo_default = Helper::const_to_client(ORM::factory('Item')->get_photo_by_size($photo_sizes['xs'], $folder));
$photo_current = Helper::const_to_client($filling_item->get_photo_by_size($photo_sizes['xs'], $folder));
$types = PhotoSizepackUploader::get_string_types(Model_Item::PHOTO_EXTENSIONS);
$maxsize = Model_Item::MAX_PHOTO_SIZE;
$tags = $filling_item->get_tags(true);
$item_section = $filling_item->catalog_id;
?>

<div class="page-item-filling <?= $page_selector ?>">
	<input type="hidden" class="js-server-values"
		data-photo-current="<?= $photo_current ?>"
		data-photo-default="<?= $photo_default ?>"
		data-image-types="<?= $types ?>"
		data-maxsize="<?= $maxsize ?>"
	>
	<form enctype="multipart/form-data" method="POST" class="js-custom-ajax-form js-custom-ajax-form_success_message" action="<?= Request::current()->url() ?>">

		<div class="custom-elems__section">
			<h2 class="custom-elems__h2">Основная информация</h2>
			<?php if ($user->has_role(array(Model_Role::ID_SUPERADMIN, Model_Role::ID_ADMIN))) { ?>
				<div class="custom-elems__string">
					<label for="status" class="custom-elems__label page-item-filling__label">Статус:</label>
					<select id="status" name="status" class="custom-elems__input">
						<?php
						foreach (Status::STATUSES_HV as $value => $title) {
							echo '<option value="' . $value . '"' . ($filling_item->status == $value ? ' selected' : '') . '>' . $title . '</option>';
						}
						?>
					</select>
				</div>
			<?php } ?>

			<div class="custom-elems__string">
				<?= ItemFilling::render_material_selection($material, $item_section) ?>
			</div>

			<div class="custom-elems__string">
				<label for="name" class="custom-elems__label page-item-filling__label">Название:</label>
				<input name="name" id="name" class="custom-elems__input" data-min="<?= Model_Item::MIN_NAME ?>" autocomplete="off"<?= $is_changing ? 'value="' . $filling_item->name . '"' : '' ?>>
			</div>

			<?php if ($material->id == Model_Item::MATERIAL_LITERATURE) { ?>
				<div class="custom-elems__string">
					<label for="pages" class="custom-elems__label page-item-filling__label">Страниц:</label>
					<input name="pages" id="pages" class="custom-elems__input" autocomplete="off"<?= $is_changing ? 'value="' . $filling_item->pages . '"' : '' ?>>
				</div>
			<?php } ?>

			<?php if ($material->id == Model_Item::MATERIAL_LITERATURE) { ?>
				<div class="page-item-filling__authors-wrap" data-values="<?= $authors_string ?>">
					<h2 class="custom-elems__h2">Авторы</h2>
					<div class="custom-elems__string hidden page-item-filling__author page-item-filling__author-template">
						<label for="author" class="custom-elems__label page-item-filling__label">Автор:</label>
						<input data-min="3" data-url="/<?= $material->alias . '/author_autocomplete' ?>" name="authors[]" id="author" class="custom-elems__input page-item-filling__author-input" autocomplete="off">
						<a class="page-item-filling__author-delete-btn"><span class="icon-cancel"></span></a>
					</div>
					<div class="page-item-filling__authors"></div>
					<div class="custom-elems__string">
						<a class="page-item-filling__author-btn black custom-elems__link custom-elems__link_type_underline-solid">Добавить автора</a>
					</div>
				</div>
			<?php } ?>

		</div>

		<div class="custom-elems__section">
			<h2 class="custom-elems__h2"><?= $image_header ?></h2>
			<div>
				<p><?= $image_text ?></p>
				<p class="page-item-filling__photo-text"></p>
			</div>
			<input type="file" name="photo" class="page-item-filling__photo">
		</div>

		<?php if ($material->id == Model_Item::MATERIAL_LITERATURE) { ?>
			<div class="custom-elems__section">
				<h2 class="custom-elems__h2">
					<label for="books">Файлы</label>
				</h2>
				<div class="uploader" data-max-size="<?= Model_Item::MAX_BOOK_SIZE ?>" data-exts="<?= implode(';', Model_Item::BOOK_EXTENSIONS) ?>" data-default-values="<?= $filling_item->files ?>"></div>
			</div>
		<?php } ?>

		<?php if ($material->id == Model_Item::MATERIAL_ARTICLE || $material->id == Model_Item::MATERIAL_NEWS) { ?>
			<div class="custom-elems__section">
				<h2 class="custom-elems__h2">
					<label>Превью</label>
				</h2>
				<div>
					<p>В списке материалов ваша публикация будет отображаться в виде карточки. На карточке будет превью текста публикации. Вы можете не указывать его, тогда превью создастся автоматически - <?= Helper::form_of_word($preview_maximum_length, 'возьмется первый', 'возьмутся первые', 'возьмутся первые') ?> <?= $preview_maximum_length ?> <?= Helper::form_of_word($preview_maximum_length, 'символ', 'символа', 'символов') ?>.
					</p>
					<input name="preview" class="page-item-filling__preview" value="<?= $filling_item->preview ?>">
				</div>
			</div>
		<? } ?>

		<div class="custom-elems__section">
			<h2 class="custom-elems__h2">
				<label><?= $description_header ?></label>
			</h2>

			<?php
			$additional_params = ($material->id == Model_Item::MATERIAL_ARTICLE || $material->id == Model_Item::MATERIAL_NEWS ? ' data-min="' . Model_Item::MIN_DESCRIPTION_OF_ARTICLE . '"' : '');
			echo HelperCKEditor::render('description', $filling_item->description, null, $additional_params)
			?>
		</div>

		<div class="custom-elems__section">
			<h2 class="custom-elems__h2">Теги</h2>
			<div>
				<p>Необходимы для возможности поиска по тегам.</p>
				<p>Теги перечисляются через пробел. Одно слово - один тег. Максимальное количество тегов - 5.</p>
				<p>
					<strong>Совет:</strong> при вводе тега вам будут предлагаться уже существующие теги. По возможности используйте их (Неиспользуемые ранее теги сначала проверяются модератором. Да и пользователям будет проще найти материал с уже привычными для них тегами).
				</p>
			</div>
			<?= ColorTags::render_input(($material->alias), $tags ? $tags : false) ?>
		</div>

		<div>
			<a class="button js-custom-ajax-form-submit-btn"><?= $is_changing ? 'Изменить' : 'Создать' ?></a>
		</div>

	</form>

</div>