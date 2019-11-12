// ------------------------------------------------
// grecaptcha - это капча от google.
//
// Важно! Для корректной работы скрипта он должен подключаться с использованием параметров &onload=grecaptcha_init&render=explicit. Вот так:
// <script src='https://www.google.com/recaptcha/api.js?hl=ru&onload=grecaptcha_init&render=explicit'></script>
// ------------------------------------------------

// Поскольку определить, какая именно капча была разгадана нельзя (чтобы сразу после нее автоматически отправить форму, например) - ведь grecaptcha_success не возвращает html node, то перед вызовом к кнопке или форме (которая и привела к вызову капчи) добавляют класс last_grecaptcha_target_class.
var last_grecaptcha_target_class = 'last_captcha_target';

// Закончила ли работу функция grecaptcha_init?
var grecaptcha_has_been_init = false;

// Действие при разгадывании капчи
function grecaptcha_success(e) {
	// https://stackoverflow.com/questions/52390562/google-recaptcha-response-uncaught-in-promise-null
	return new Promise(function (resolve, reject) {
		if (grecaptcha === undefined) {
			reject();
		}
		var response = grecaptcha.getResponse();
		if (!response) {
			reject();
		}

		$('.g-recaptcha').trigger('grecaptcha-success', e);

		resolve();
	});
}

// Используется для инициализации капч. Вызывается сама - за счет "onload=grecaptcha_init&render=explicit" - при загрузке документа
function grecaptcha_init() {
	var $captcha = $('.g-recaptcha');
	render_grecaptcha($captcha);
	$(document).trigger('grecaptcha_init');
	grecaptcha_has_been_init = true;
}

/*
	Превратить div в recaptcha. Я делаю это вручную, так как есть динамично появляющиеся капчи (например, открытие страницы login через euv_custom_popup). Если делать автоматически, то в таком случае нельзя будет отловить grecaptcha_has_been_init.
	А зачем нужен grecaptcha_has_been_init?
	grecaptcha_has_been_init говорит о том, что функция grecaptcha_init сработала, а это значит, что можно использовать функцию render_grecaptcha.
  Если попытаться вызвать render_grecaptcha до grecaptcha_has_been_init, то будет ошибка.
*/
function render_grecaptcha($captcha) {
	if ($captcha.length && !$captcha.hasClass('g-recaptcha_is-active')) {
		grecaptcha.render($captcha[0], {
			'sitekey': $captcha.attr('data-sitekey'),
			'callback': $captcha.attr('data-callback')
		});
		$captcha.addClass('g-recaptcha_is-active');
	}
}