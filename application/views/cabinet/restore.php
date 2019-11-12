<div class="page-forgot">
	<form method="POST" class="js-custom-ajax-form js-custom-ajax-form_success_message" action="/restore?token=<?= $_GET['token'] ?>">
		<h1 class="custom-elems__h1"><?= $seo_data['h1'] ?></h1>
		<div class="custom-elems__string  custom-elems__string_gap_lg">
			<div>
				<label for="password">Придумайте новый пароль:</label>
			</div>
			<div>
				<input id="password" name="password" type="password" class="input-style-1 custom-elems__input"/>
			</div>
		</div>
		<div class="custom-elems__string  custom-elems__string_gap_lg">
			<div>
				<label for="password_confirm">Новый пароль еще раз:</label>
			</div>
			<div>
				<input id="password_confirm" name="password_confirm" type="password" class="input-style-1 custom-elems__input"/>
			</div>
		</div>
		<div class="custom-elems__string custom-elems__string_gap_lg">
			<input type="submit" class="button js-custom-ajax-form-submit-btn" value="Изменить пароль"/>
		</div>
	</form>

</div>