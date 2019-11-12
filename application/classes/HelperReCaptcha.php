<?php

class HelperReCaptcha {

	const FIELD_NAME = 'g-recaptcha-response';
	const SESSION_NAME = 'captcha';
	const SESSION_TIMEOUT = 120;

	static public function render($user) {
		// !$user надо - так как в эту функцию мы можем отправить null
		if (!$user || !$user->is_admin()) {
			$sitekey = Kohana::$config->load('recaptcha')['site_key'];
			return '<div class="g-recaptcha" data-field-name="' . static::FIELD_NAME . '" data-callback="grecaptcha_success" data-sitekey="' . $sitekey . '"></div>';
		} else {
			return false;
		}
	}

	/**
	 * Добавляет к объекту $validation правило - функцию check_captcha.
	 * $user указывается, так как для админа капчу вводить не обязательно.
	 */
	static public function add_rule($validation) {
		return $validation->rule(static::FIELD_NAME, 'HelperReCaptcha::check_captcha', array(':value'));
	}

	static public function check_captcha_session() {
		$captcha = Session::instance()->get(static::SESSION_NAME);
		return ($captcha && $captcha['timeout'] > time() && $captcha['status'] && CHECK_CAPTCHA) || !CHECK_CAPTCHA;
	}

	static public function check_captcha($value) {
		if (CurrentUser::get_user()->is_admin() || static::check_captcha_session()) {
			return true;
		}

		$secret_key = Kohana::$config->load('recaptcha')['secret_key'];
		$response = null;

		$reCaptcha = new ReCaptcha($secret_key);
		if ($value) {
			$response = $reCaptcha->verifyResponse(
				$_SERVER["REMOTE_ADDR"],
				$value
			);
		}
		if ($response != null && $response->success) {
			Session::instance()->set(static::SESSION_NAME, array('status' => true, 'timeout' => time() + static::SESSION_TIMEOUT));
			return true;
		} else {
			throw new CaptchaException();
		}
	}

}