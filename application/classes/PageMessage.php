<?php

/**
 * Class PageMessage
 * Класс для отправки браузеру сообщения на отдельной странице. Используется для custom-ajax (js-custom-ajax-form_success_message) - в случае render_ajax.
 */
class PageMessage {

	const VIEW = 'pages/message';

	private $page;
	private $data;

	public function get_data() {
		return $this->data;
	}

	public function get_page() {
		return $this->page;
	}

	public function __construct($params = array()) {
		$this->data = $params;
		$this->page = Helper::load_page(VIEWSPATH . static::VIEW . '.php', $params);
	}

}
