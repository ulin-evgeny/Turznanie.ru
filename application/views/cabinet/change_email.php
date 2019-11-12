<div class="page-change-email">

	<form method="POST" action="/cabinet/change_email" class="js-custom-ajax-form js-custom-ajax-form_success_message">
		<div class="custom-elems__section">
			<div class="custom-elems__string">
				<span class="custom-elems__label page-cabinet__label_length_long custom-elems__label">Текущий Email:</span>
				<span><?= $person->email ?></span>
			</div>
			<div class="custom-elems__string">
				<span class="custom-elems__label page-cabinet__label_length_long custom-elems__label">Новый Email:</span>
				<input class="custom-elems__input" name="new_email"/>
			</div>
		</div>
		<div class="custom-elems__section">
			<a href="#" class="button js-custom-ajax-form-submit-btn">Изменить</a>
		</div>
	</form>

</div>