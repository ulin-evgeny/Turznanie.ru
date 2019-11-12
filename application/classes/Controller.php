<?php

class Controller extends Kohana_Controller {

	const TEMPLATE_MAIN = 'main';
	const TEMPLATE_AJAX = 'ajax';

	public $seo_page;
	public $seo_data = array();
	public $breadcrumbs = array();

	public $user = null;
	public $errors = null;
	public $template_extra = null;
	public $template_main = Controller::TEMPLATE_MAIN;

	public $fullpage = false;

	public function before() {
		$this->user = CurrentUser::get_user();

		// Важный момент! Если caching НЕ ОТКЛЮЧЕН при is_ajax(), то будет проблема. Опишу на примере. Если я открою каталог статей. И сделаю что-то через AJAX, например, перейду на следующую страницу. Потом открою страницу статьи. После чего нажму кнопку "Назад" в браузере. То у меня откроется страница без шаблона (без вьюхи main.php, в которой подключаются стили, скрипты и все остальное). Просто контент каталога (который должен был обновиться) на белом фоне.
		if (!IN_PRODUCTION || $this->request->is_ajax()) {
			$this->response->headers('Cache-Control', 'no-cache,no-store,max-age=0,must-revalidate');
			$this->response->headers('Pragma', 'no-cache');;
		}

		if (!$this->request->is_ajax()) {
			Session::instance()->delete(HelperReCaptcha::SESSION_NAME);
		}

		return parent::before();
	}

	public function after() {
		return parent::after();
	}

	public function render($template, $data = array()) {
		$seo_page = Model_Seo::get_page_by_url($this->request->url());
		if ($seo_page->loaded()) {
			$seo_data = Model_Seo::get_seo_data_by_page($seo_page);
			foreach ($seo_data as $key => $val) {
				if (!isset($this->seo_data[$key])) {
					$this->seo_data[$key] = $seo_data[$key];
				}
			}
			if (empty($this->breadcrumbs)) {
				$this->breadcrumbs = $seo_page->get_breadcrumbs();
			}
		}

		$view = View::factory($this->template_main)
			->set('template', $template)
			->set('template_extra', $this->template_extra)
			->set('user', $this->user)
			->set('seo_page', $this->seo_page)
			->set('errors', $this->errors)
			->set('seo_data', $this->seo_data)
			->set('fullpage', $this->fullpage)
			->set('breadcrumbs', $this->breadcrumbs)
			->set($data);

		$this->response->body($view);
	}

	public function render_ajax($message, $status = Ajax::STATUS_SUCCESS) {
		/*
		Зачем тут нужна проверка - ajax или нет?
		Представь, что я добавил свой Item. Читаю его. В это время админ его удаляет. Я нахожу опечатку, хочу исправить. Жму "Изменить". Должна вывестись ошибка в виде custom-popup, говорящая о том, что не найдена запись в БД (читай код изменения Item'a - там ошибки выводятся через render_ajax). Проблема в том, что со страницы Item'а ссылка "Изменить" - это не AJAX.
		Поэтому делаем проверку.
		Если не AJAX, то надо просто рендерить страницу с сообщением вместо custom-popup'a.
		Если, конечно, это сообщение, а не массив из элементов page_message__text, page_message__btn_text, page_message__btn_href.
		*/
		if ($this->request->is_ajax()) {
			if (is_a($message, 'PageMessage')) {
				$message = $message->get_page();
			}
			return $this->response->body(json_encode(array('status' => $status, 'message' => $message)));
		} else {
			// STATUS_UNSUCCESS для простого вывода ошибок. STATUS_SUCCESS - для вывода сообщений (js-custom-ajax-form_success_message)
			if ($status === Ajax::STATUS_UNSUCCESS || $status === Ajax::STATUS_SUCCESS) {
				switch (true) {
					case is_string($message):
						return $this->render('pages/message', array(
							'text' => $message
						));
						break;
					case is_a($message, 'PageMessage'):
						return $this->render('pages/message', $message->get_data());
						break;
				}
			}
			return $this->render('pages/message', array(
					'text' => 'Ошибка с redirect\'ом. Пожалуйста, свяжитесь с нами, чтобы мы могли исправить ее. Напишите нам, что вы сделали, чтобы получить эту ошибку.',
					'btn_href' => $GLOBALS['aliases']['contact_us'],
					'btn_text' => 'Сообщить'
				)
			);
		}
	}

	public function go_home() {
		return $this->custom_redirect('/');
	}

	public function reload_page() {
		return $this->custom_redirect($this->request->uri(), 303);
	}

	public function go_back() {
		Valid::url(Request::initial()->referrer()) OR $this->go_home();
		return $this->custom_redirect(Request::initial()->referrer());
	}

	/**
	 * Редирект с учетом того, что его может вызывать код, к которому обратились через AJAX.
	 * @param string $uri
	 * @param int $code
	 * @return mixed
	 */
	public function custom_redirect($uri = '', $code = 302) {
		if ($this->request->is_ajax()) {
			return $this->render_ajax(array(
				'url' => $uri),
				Ajax::STATUS_REDIRECT);
		} else {
			parent::redirect($uri, $code);
		}
	}

	public function not_found($text = 'Not found') {
		throw HTTP_Exception::factory(404, $text);
	}

	public static function get_include($template, $template_extra = false) {
		if ($template_extra != NULL) {
			return VIEWSPATH . 'template_extra/' . $template_extra . '.php';
		} else {
			return VIEWSPATH . $template . '.php';
		}
	}

	public static function get_controller_name() {
		if (static::class == 'Controller') {
			return static::class;
		} else {
			return str_replace("Controller_", "", static::class);
		}
	}

	/*
		Данная функция нужна для того, чтобы окончить скрипт в before (чтобы после before не вызывался action_index или еще какой action)
	*/
	public function action_nothing() {
		return;
	}

}
