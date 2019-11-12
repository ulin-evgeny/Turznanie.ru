<?php

class CustomEmail extends PHPMailer\PHPMailer\PHPMailer {

	public $CharSet = 'utf-8';
	public $ContentType = 'text/html';
	public $SMTPAuth = true;
	public $Mailer = 'smtp';

	const TO_EMAIL = 0;
	const TO_USERNAME = 1;

	function __construct() {
		$config = Kohana::$config->load('email');
		$this->FromName = $GLOBALS['SITE_INFO']['title'];
		$this->From = $config['username'];
		$this->Username = $config['username'];
		$this->Password = $config['password'];
		$this->Host = $config['hostname'];
		$this->Port = $config['port'];
		$this->SMTPSecure = $config['smtpsecure'];
		parent::__construct();
	}

	public function add_intro() {
		$username = $this->to[0][static::TO_USERNAME];
		$this->Body = '<p>Здравствуйте, ' . $username . '!</p>' . $this->Body;
		return;
	}

	public function add_signature() {
		$this->Body .= '<p>С уважением, администрация сайта <a href="' . $this->FromName . '">' . $this->FromName . '</a>' . '.</p>';
		return;
	}

	public function send($params = []) {
		// установка значений по умолчанию
		$values['intro'] = isset($params['intro']) ? $params['intro'] : true;
		$values['signature'] = isset($params['signature']) ? $params['signature'] : true;
		$values['dont_answer'] = isset($params['dont_answer']) ? $params['dont_answer'] : true;
		$values['unsubscribe'] = isset($params['unsubscribe']) ? $params['unsubscribe'] : false;

		if ($values['intro']) {
			$this->add_intro();
		}

		$this->AltBody = $this->Body;

		if ($values['signature']) {
			$this->add_signature();
		}

		if ($values['dont_answer']) {
			$this->Body .= '<p style="font-size:10px;">Не отвечайте на это сообщение. Если вы хотите связаться с администрацией, напишите письмо по адресу: ' . $GLOBALS['SITE_INFO']['email'] . '</p>';
		}

		if ($values['unsubscribe']) {
			$user = ORM::factory('User')->where('username', '=', $this->to[0][static::TO_USERNAME])->find();
			$this->Body .= '<p style="font-size:10px;">Для управления рассылкой зайдите в личный кабинет (для этого нужно авторизоваться), а затем перейдите в "Настройки рассылок".<br>Для быстрого отключения рассылки пройдите по ссылке ниже:<br>' . $user->get_link_unsubscribe() . '<br>В дальнейшем вы сможете снова включить рассылку - в вышеупомянутом разделе личного кабинета.</p>';
		}

		return parent::Send();
	}

	/**
	 * Функция устанавливает тему письма и его тело из указанного файла, который находится в MAILSPATH.
	 * Зачем я вынес письма в файлы? Чтобы, если что, их можно было перевести на другой язык. Да и в отдельном файле проще разобраться, нежели в php коде. Да и некоторые письма могут повторяться в разных php функциях, классах.
	 * @param $filename
	 * @param $data
	 */
	public function set_subject_and_body($filename, $data) {
		$string = Helper::load_page(MAILSPATH . $filename . '.php', $data);
		$dom = HelperText::create_dom($string);

		$subject = $dom->getElementById('subject');
		$subject = HelperText::get_dom_node_inner_html($dom, $subject);
		$body = $dom->getElementById('body');
		$body = HelperText::get_dom_node_inner_html($dom, $body);

		$this->Subject = $subject;
		$this->Body = $body;
	}

}
