<div class="page-change-page">
	<form enctype="multipart/form-data" method="POST" class="js-custom-ajax-form js-custom-ajax-form_success_message" action="<?= $sending_url ?>">
		<h2 class="custom-elems__h1"><?= $seo_data['h1'] ?></h2>

		<div class="custom-elems__section">
			<div class="custom-elems__string">
				<div>
					<label for="title" class="custom-elems__label">Мета-тег Title:</label>
				</div>
				<div>
					<input name="title" id="title" class="custom-elems__input" autocomplete="off" value="<?= $editable_seo_page_data['title'] ?>">
				</div>
			</div>

			<div class="custom-elems__string">
				<div>
					<label for="description" class="custom-elems__label">Мета-тег Description:</label>
				</div>
				<div>
					<input name="description" id="description" class="custom-elems__input" autocomplete="off" value="<?= $editable_seo_page_data['description'] ?>">
				</div>
			</div>

			<div class="custom-elems__string">
				<div>
					<label for="keywords" class="custom-elems__label">Keywords:</label>
				</div>
				<div>
					<input name="keywords" id="keywords" class="custom-elems__input" autocomplete="off" value="<?= $editable_seo_page_data['keywords'] ?>">
				</div>
			</div>

			<div class="custom-elems__string">
				<div>
					<label for="h1" class="custom-elems__label">Заголовок h1:</label>
				</div>
				<div>
					<input name="h1" id="h1" class="custom-elems__input" autocomplete="off" value="<?= $editable_seo_page_data['h1'] ?>">
				</div>
			</div>

			<div class="custom-elems__string">
				<div>
					<label for="title_menu" class="custom-elems__label">Title для меню (не обязательно - тогда возьмется обычный Title)</label>
				</div>
				<div>
					<input name="title_menu" id="title_menu" class="custom-elems__input" autocomplete="off" value="<?= $editable_seo_page_data['title_menu'] ?>">
				</div>
			</div>

			<div class="custom-elems__string">
				<div>
					<label for="content" class="custom-elems__label">Контент страницы:</label>
				</div>
				<div>
					<?= HelperCKEditor::render('content', $editable_seo_page_data['content']) ?>
				</div>
			</div>
		</div>

		<div class="custom-elems__string">
			<a class="button js-custom-ajax-form-submit-btn">Сохранить</a>
		</div>

	</form>

</div>