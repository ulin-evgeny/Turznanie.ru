<?php
$agreement_page = ORM::factory('Seo', Model_Seo::ID_AGREEMENT);
?>
<div class="page-registration">

	<h1 class="custom-elems__h1 page-registration__header">Регистрация</h1>
	<div class="page-registration__agreement agreement">
		<h2 class="custom-elems__h2"><?= $agreement_page->title ?></h2>
		<div>
			<?= $agreement_page->content ?>
		</div>
	</div>

	<form method="POST" class="js-custom-ajax-form page-registration__form js-custom-ajax-form_success_message" action="/registration">
		<div class="custom-elems__string custom-elems__string_gap_lg">
			<label for="email" class="custom-elems__label page-registration__label">Email:</label>
			<input name="email" id="email" class="custom-elems__input tooltip js-validate">
		</div>
		<div class="custom-elems__string custom-elems__string_gap_lg">
			<label for="username" data-label="username" class="custom-elems__label page-registration__label">Логин:</label>
			<input name="username" id="username" class="custom-elems__input tooltip js-validate">
		</div>
		<div class="custom-elems__string custom-elems__string_gap_lg">
			<label for="password" class="custom-elems__label page-registration__label">Пароль:</label>
			<input type="password" name="password" id="password" class="custom-elems__input js-validate tooltip js-validate-extra-1">
		</div>
		<div class="custom-elems__string custom-elems__string_gap_lg">
			<label for="password_confirm" class="custom-elems__label page-registration__label">Пароль (еще раз):</label>
			<input type="password" name="password_confirm" id="password_confirm" class="custom-elems__input tooltip js-validate-with-extra-1">
		</div>

		<div class="custom-elems__string custom-elems__string_gap_lg">
			<input style="vertical-align: middle;" name="agreement" id="agreement" type="checkbox">
			<label for="agreement">Я подтверждаю, что ознакомлен со всеми пунктами пользовательского Соглашения и безусловно принимаю их.</label>
		</div>

		<div class="custom-elems__string custom-elems__string_gap_lg">
			<a class="button js-custom-ajax-form-submit-btn">Отправить</a>
		</div>
	</form>

</div>
