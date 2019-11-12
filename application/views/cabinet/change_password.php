<div class="page-change-password">

	<form method="POST" action="/cabinet/change_password" class="js-custom-ajax-form js-custom-ajax-form_success_message">
		<div class="custom-elems__section">
			<div class="custom-elems__string">
				<span class="custom-elems__label page-cabinet__label_length_long custom-elems__label">Старый пароль:</span>
				<input class="custom-elems__input" name="password_old" type="password"/>
			</div>
			<div class="custom-elems__string">
				<span class="custom-elems__label page-cabinet__label_length_long custom-elems__label">Новый пароль:</span>
				<input class="custom-elems__input" name="password" type="password"/>
			</div>
			<div class="custom-elems__string">
				<span class="custom-elems__label page-cabinet__label_length_long custom-elems__label">Пароль еще раз:</span>
				<input class="custom-elems__input" name="password_confirm" type="password"/>
			</div>
		</div>
		<div class="custom-elems__section">
			<a href="#" class="button js-custom-ajax-form-submit-btn">Сохранить</a>
		</div>
	</form>

</div>