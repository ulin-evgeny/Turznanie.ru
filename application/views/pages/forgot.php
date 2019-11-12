<div class="page-forgot">

	<form method="POST" class="js-custom-ajax-form js-custom-ajax-form_success_message" action="/forgot">
		<h1 class="custom-elems__h1"><?= $seo_data['h1'] ?></h1>
		<?php
		if (isset($show_message)) { ?>
			<div class="page-login__message">Пользователя с таким почтовым ящиком не существует.</div>
		<?php } ?>
		<div class="custom-elems__string  custom-elems__string_gap_lg">
			<div>
				<label for="email">Email, который был привязан к аккаунту:</label>
			</div>
			<div>
				<input name="email" id="email" class="input-style-1 custom-elems__input"/>
			</div>
		</div>
		<div class="custom-elems__string custom-elems__string_gap_lg">
			<a type="submit" class="button js-custom-ajax-form-submit-btn">Восстановить</a>
		</div>
	</form>

</div>