<div class="page-communicate">
	<h1 class="custom-elems__h1"><?= $seo_data['h1'] ?></h1>

	<form method="POST" class="js-custom-ajax-form js-custom-ajax-form_success_message" action="/ContactUs">
		<div class="custom-elems__section">
			<?php if (!$user->loaded()) { ?>
				<div class="custom-elems__string">
					<div>
						<label for="email" class="custom-elems__label">Ваш Email:</label>
					</div>
					<div>
						<input name="email" id="email" class="custom-elems__input custom-elems__input_width_full">
					</div>
				</div>
			<?php } ?>
			<div class="custom-elems__string">
				<div>
					<label for="subject" class="custom-elems__label">Тема письма:</label>
				</div>
				<div>
					<input name="subject" id="subject" class="custom-elems__input custom-elems__input_width_full">
				</div>
			</div>
			<div class="custom-elems__string">
				<div>
					<label for="body" class="custom-elems__label">Текст письма:</label>
				</div>
				<div>
					<?= HelperCKEditor::render('body') ?>
				</div>
			</div>
		</div>
		<div>
			<a class="button js-custom-ajax-form-submit-btn">Отправить</a>
		</div>
	</form>

</div>
