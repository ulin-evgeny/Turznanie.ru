<?php
$context_class = isset($context) ? 'page-login_context_' . $context : 'page-login_context_0';
$is_ajax = Request::initial()->is_ajax();
?>

<div class="page-login <?= $context_class ?>">

	<form method="POST" class="page-login__form js-custom-ajax-form" action="/login_handle">
		<h1 class="custom-elems__h1"><?= $is_ajax ? 'Быстрый вход' : $seo_data['h1'] ?></h1>
		<?php
		if (!$is_ajax) {
			if ($message) { ?>
				<div class="page-login__message"><?= $message ?></div>
			<?php }
		} else { ?>
			<input name="can_go_back" value="true" type="hidden"/>
		<?php } ?>
		<div class="custom-elems__section">
			<div class="custom-elems__string custom-elems__string_gap_lg">
				<label class="page-login__label custom-elems__label" for="username">Логин:</label>
				<input name="username" id="username" class="input-style-1 custom-elems__input" value="<?= isset($username) ? $username : '' ?>"/>
			</div>
			<div class="custom-elems__string custom-elems__string_gap_lg">
				<label class="page-login__label custom-elems__label" for="password">Пароль:</label>
				<input type="password" id="password" name="password" class="custom-elems__input"/>
			</div>
		</div>

		<div class="custom-elems__section">
			<input type="submit" class="button js-custom-ajax-form-submit-btn" value="Войти">
		</div>
		<div>
			<div class="custom-elems__string">
				Нет учетной записи?
				<a class="black custom-elems__link custom-elems__link_type_underline-solid" href="/registration">Зарегистрируйтесь!</a>
			</div>
			<div class="custom-elems__string">
				Забыли пароль? Вы можете
				<a class="black custom-elems__link custom-elems__link_type_underline-solid" href="/forgot">восстановить</a> его.
			</div>
		</div>
		<div class="hidden page-login__captcha">
			<?= HelperReCaptcha::render(null); ?>
		</div>
	</form>

</div>