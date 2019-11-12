<div id="subject">
	Регистрация на сайте <?= $GLOBALS['SITE_INFO']['title'] ?>
</div>

<div id="body">
	<p>
		Вы были зерегестрированы на сайте <?= $GLOBALS['SITE_INFO']['title'] ?>
		<br><br>
		Ваши регистрационные данные:
		<br>
		<b>Логин:</b> <?= $_POST['username'] ?>
		<br>
		<b>Пароль:</b> <?= $_POST['password'] ?>
		<br><br>
		Для подтверждения Email пройдите по ссылке:
		<br>
		<a href="<?= $url ?>"><?= $url ?></a>
	</p>
</div>
