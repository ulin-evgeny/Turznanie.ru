<?php

class Controller_Users extends Controller {

	const LOGIN_ATTEMPT_TIME = 10 * 60; // за сколько времени эти три попытки совершаются (и сколько нужно ждать после последней неудачной попытки, чтобы перестала появляться капча).
	const LOGIN_ATTEMPT_AMOUNT = 3; // 3 попытки до капчи

	const TIME_TO_SEND_APPROVE_MESSAGE = 120; // как часто можно отправлять письмо для подтверждения Email

	public function action_request_approve_mail() {
		$user_action = ORM::factory('UserAction')->where('id', '=', $this->user->id)->where('action_id', '=', Model_Action::ID_REQUEST_APPROVE_MAIL)->find();
		if (($user_action->loaded() && strtotime($user_action->time) + static::TIME_TO_SEND_APPROVE_MESSAGE < time()) || !$user_action->loaded()) {
			$user_action->id = $this->user->id;
			$user_action->action_id = Model_Action::ID_REQUEST_APPROVE_MAIL;
			$user_action->time = Helper::unix_time_to_mysql_time(time());
			$user_action->save();

			// Отправка письма
			$mail = new CustomEmail();
			$mail->set_subject_and_body('approve_email', array('url' => $this->user->get_token_approve_url()));
			$mail->addAddress($this->user->email, $this->user->username);

			if (!$mail->send()) {
				return $this->render_ajax("Mailer Error: " . $mail->ErrorInfo, Ajax::STATUS_UNSUCCESS);
			}

			return $this->render_ajax('ok');
		} else {
			$time_left = (strtotime($user_action->time) + static::TIME_TO_SEND_APPROVE_MESSAGE) - time();
			return $this->render_ajax('Мы отправляли вам письмо недавно, подождите ' . $time_left . ' ' . Helper::form_of_word($time_left, 'секунду', 'секунды', 'секунд') . '.' . Messages::CHECK_SPAM_STRING, Ajax::STATUS_UNSUCCESS);
		}
	}

	public function action_logout() {
		if ($this->user->loaded()) {
			Auth::instance()->logout();
		}
		$this->go_back();
	}

	public function action_forgot() {
		if ($this->user->loaded()) {
			return $this->go_home();
		}
		if ($_POST) {
			// Валидация
			$validation = Validation::factory($_POST)
				->rule('email', 'not_empty')
				->rule('email', 'email')
				->rule('email', 'CustomValidation::exist_user_email', array(':value', ':field', ':validation'));
			try {
				$validation->check_with_captcha();
			} catch (ORM_Validation_Exception $e) {
				return $this->render_ajax($e->errors('validation'), Ajax::STATUS_UNSUCCESS);
			} catch (CaptchaException $e) {
				return $this->render_ajax(HelperReCaptcha::render(null), Ajax::STATUS_NEED_CAPTCHA);
			}

			$addressee = ORM::factory('User')->where('email', '=', $_POST['email'])->find();

			// Токен
			$token = password_hash($addressee->username . $addressee->password, PASSWORD_BCRYPT);
			$addressee->token_restore = $token;
			$addressee->token_restore_time = date('Y-m-d H:i:s');
			$addressee->save();

			// Письмо
			$link = Helper::get_site_url() . '/restore?token=' . $token;
			$link_active_minutes = Model_User::TOKEN_RESTORE_LIFETIME / 60;
			$link_active_text = 'Ссылка будет активна в течение ' . $link_active_minutes . ' ' . Helper::form_of_word($link_active_minutes, 'минуты', 'минут', 'минут') . '.';

			$mail = new CustomEmail();
			$mail->addAddress($_POST['email'], $addressee->username);
			$mail->set_subject_and_body('restore', array('link' => $link, 'link_active_text' => $link_active_text));
			$mail->send();

			return $this->render_ajax(new PageMessage(array(
				'text' => 'На указанный Email адрес было отправлено сообщение с ссылкой для указания нового пароля. ' . $link_active_text . Messages::CHECK_SPAM_STRING
			)));
		}
		return $this->render('pages/forgot');
	}

	public function action_change_email() {
		if (!isset($_GET['token']) || !$_GET['token']) {
			return $this->go_home();
		}

		$user = ORM::factory('User')->where('token_change_email', '=', $_GET['token'])->find();
		if (!$user->loaded()) {
			return $this->render_ajax(new PageMessage(array(
					'text' => 'Пользователя с таким токеном не существует.'
				)
			));
		}

		$user->token_change_email = null;

		if (strtotime($user->token_change_email_time) + Model_User::TOKEN_CHANGE_EMAIL_LIFETIME < time()) {
			$user->new_email = null;
			$user->token_change_email_time = null;
			$user->save();
			return $this->render_ajax('Время действия ссылки истекло.');
		}

		$user->email = $user->new_email;
		$user->new_email = null;
		$user->token_change_email_time = null;
		$user->save();

		return $this->render_ajax(new PageMessage(array(
			'text' => 'Email успешно изменен!',
		)));
	}


	public function action_restore() {
		if (!isset($_GET['token']) || !$_GET['token']) {
			return $this->go_home();
		}

		$user = ORM::factory('User')->where('token_restore', '=', $_GET['token'])->find();
		if (!$_GET['token'] || !$user->loaded()) {
			return $this->render_ajax(new PageMessage(array(
					'text' => 'Пользователя с таким токеном не существует.'
				)
			));
		}

		$user->token_restore = null;

		if (strtotime($user->token_restore_time) + Model_User::TOKEN_RESTORE_LIFETIME < time()) {
			$user->token_restore_time = null;
			$user->save();
			return $this->render_ajax(new PageMessage(array(
				'text' => 'Время действия ссылки истекло.'
			)));
		}

		if ($_POST) {
			try {
				$user->change_password();
			} catch (ORM_Validation_Exception $e) {
				return $this->render_ajax($e->errors('models'), Ajax::STATUS_UNSUCCESS);
			}

			$user->token_restore_time = null;
			$user->save();

			return $this->render_ajax(new PageMessage(array(
				'text' => 'Пароль успешно изменен!'
			)));
		} elseif (!$_POST) {
			return $this->render('cabinet/restore');
		}
	}

	/**
	 * Возвращает true если капча НУЖНА, false - если не нужна
	 * @param $user
	 * @return bool
	 */
	static public function need_captcha_by_user_bool($user) {
		return $user && $user->login_attempt_amount >= static::LOGIN_ATTEMPT_AMOUNT && strtotime($user->login_attempt_time) + static::LOGIN_ATTEMPT_TIME >= time() && !HelperReCaptcha::check_captcha_session();
	}

	public function action_login() {
		if ($this->user->loaded()) {
			return $this->custom_redirect('/cabinet');
		}
		/*
		Механизм сделан через сессии и дополнительный action, потому что...
		Если написать что-то в форме и отправить ее. Страница перезагрузится (если эта страница без AJAX), после чего если нажать F5 или Ctrl+R или Ctrl+Shift+R (это в Chrome. хоткеи стандартные), то форма снова (!) отправляется. Ну, на сервер $_POST приходит. А ведь не должен приходить, мы же не нажимали кнопку "Отправить", а перезагрузили страницу.
		*/
		$session = Session::instance()->get('login');
		$message = isset($session['message']) ? $session['message'] : '';
		$username = isset($session['username']) ? $session['username'] : '';
		$this->render('pages/login', array(
			'message' => $message,
			'username' => $username
		));
		Session::instance()->delete('login');
	}

	public function action_login_handle() {
		if ($this->user->loaded()) {
			// если это обычный запрос, то происходит редирект в кабинет, если ajax, то просто обновление текущей страницы (читай функцию render).
			return $this->custom_redirect('/cabinet');
		}

		if ($_POST) {
			$username = $_POST['username'];
			$user = ORM::factory('User')->where('username', '=', $username)->find();

			if (strtotime($user->login_attempt_time) + static::LOGIN_ATTEMPT_TIME < time() && $user->loaded()) {
				$user->login_attempt_amount = 0;
				$user->login_attempt_time = Helper::unix_time_to_mysql_time(time());
				$user->save();
			}

			$require_captcha_bool = static::need_captcha_by_user_bool($user);
			if ($require_captcha_bool) {
				$validation = new Validation($_POST);
				try {
					$validation->check_with_captcha();
				} catch (CaptchaException $e) {
					return $this->render_ajax(HelperReCaptcha::render(null), Ajax::STATUS_NEED_CAPTCHA);
				}
			}

			// Если значения логина и пароля не пустые, то пытаемся авторизоваться на сайте
			if (!empty($_POST['username']) && !empty($_POST['password'])) {
				Auth::instance()->login($_POST['username'], $_POST['password']);
			}

			if (Auth::instance()->logged_in()) {
				// Если авторизация прошла успешно
				if (isset($_POST['can_go_back'])) {
					// если пользователь авторизовался через popup
					return $this->go_back();
				} else {
					// если пользователь авторизовался через полноценную страницу входа
					return $this->go_home();
				}
			} else {
				// Если авторизация прошла неуспешно
				if ($user->loaded()) {
					$user->login_attempt_time = Helper::unix_time_to_mysql_time(time());
					$user->login_attempt_amount += 1;
					$user->save();
				}
				$session['message'] = 'Неверный логин или пароль';
			}

			if ($username) {
				$session['username'] = $username;
			}
			Session::instance()->set('login', $session);

			return $this->render_ajax(array('url' => '/login'), Ajax::STATUS_REDIRECT);
		}
	}

	public function action_registration() {
		if ($this->user->loaded()) {
			$this->go_home();
		}
		if ($_POST) {
			$token_approve = password_hash(time() . $_POST['username'] . $_POST['email'], PASSWORD_BCRYPT);

			$data = array(
				'username' => $_POST['username'],
				'email' => $_POST['email'],
				'password' => $_POST['password'],
				'password_confirm' => $_POST['password_confirm'],
				'token_approve' => $token_approve
			);

			$user = ORM::factory('User');
			$user->values($data, array('username', 'email', 'password', 'token_approve'));

			$validation = new Validation(array(
				'password' => $_POST['password'],
				'password_confirm' => $_POST['password_confirm']
			));
			$validation->labels($user->labels());
			$validation->rules('password', Model_Auth_User::get_password_rules());
			$validation->rules('password_confirm', Model_Auth_User::get_password_confirm_rules());

			try {
				$user->check($validation);
			} catch (ORM_Validation_Exception $e) {
				return $this->render_ajax($e->errors('models'), Ajax::STATUS_UNSUCCESS);
			}

			if (!isset($_POST['agreement'])) {
				return $this->render_ajax('Вы не приняли соглашение', Ajax::STATUS_UNSUCCESS);
			}

			try {
				$validation_captcha = HelperReCaptcha::add_rule(new Validation($_POST));
				$validation_captcha->check();
			} catch (CaptchaException $e) {
				return $this->render_ajax(HelperReCaptcha::render(null), Ajax::STATUS_NEED_CAPTCHA);
			}

			$user->save();
			$user->add('roles', ORM::factory('Role', array('name' => 'login')));

			/*
			$user_notifications необходимо создавать здесь, а не при в изменении поля, так как по умолчанию поля равны 1 (true). а если не создать, то будет 0 (false) (а то можно было бы не писать этот код - просто при изменении поля еще и устанавливать id (который означает user_id), и если запись не загрузится (ее нет в БД), то создастся новая (save()).
			Кроме того, пользователю нужно сгенерировать токен для отписки.
			*/
			$user_notifications = ORM::factory('UserNotification');
			$user_notifications->id = $user->id;
			$user_notifications->token_unsubscribe = password_hash($_POST['email'] . $_POST['username'] . time(), PASSWORD_BCRYPT);
			$user_notifications->save();

			// Отправка письма
			$url = $user->get_token_approve_url();
			$mail = new CustomEmail();
			$mail->set_subject_and_body('registration', array('url' => $url));
			$mail->addAddress($user->email, $user->username);
			$mail->send(array('intro' => false));

			return $this->render_ajax(new PageMessage(array(
				'text' => 'Вы успешно зарегистрировались! Но для того, чтобы стать полноценным пользователем, необходимо подтвердить Email. Мы отправили вам письмо. Для подтверждения Email пройдите по ссылке в нем.' . Messages::CHECK_SPAM_STRING,
				'btn_href' => '/login',
				'btn_text' => 'Войти'
			)));
		}
		return $this->render('pages/registration');
	}

	public function action_approve() {
		$token = $this->request->query('token');
		if ($token) {
			// Ищем пользователя с нужным токеном
			$user = ORM::factory('User')->where('token_approve', '=', $token)->find();
			if ($user->loaded()) {
				$role_user = new Model_Role(array('id' => Model_Role::ID_APPROVED));
				if (!$user->has('roles', $role_user)) {
					$user->add('roles', ORM::factory('Role', array('name' => 'approved')));

					// Можно сразу и авторизовать и перенаправить ЛК
					// Auth::instance()->force_login($user->get('username'));
					// $this->custom_redirect("/cabinet");
					// Или переадресовать на форму входа для ввода логина и пароля
					//$this->custom_redirect("/users/login");

					$page_message__text = 'Спасибо за подтверждение Email! Теперь вы полноценный пользователь!';
				} else {
					$page_message__text = 'Ошибка! Этот Email уже подтвержден.';
				}
				return $this->render('pages/message', array(
						'text' => $page_message__text
					)
				);
			}
		}
		return $this->custom_redirect('/');
	}

	public function action_unsubscribe() {
		$token = $_GET['token'];
		if (!$token) {
			return $this->custom_redirect('/');
		}
		$this->template_extra = null;
		$user_notifications = ORM::factory('UserNotification')->where('token_unsubscribe', '=', $token)->find();
		if ($user_notifications->loaded()) {
			$user_notifications->material_status = 0;
			$user_notifications->comment_status = 0;
			$user_notifications->comment_add = 0;
			$user_notifications->mention_in_comment = 0;
			$user_notifications->save();
			$user = ORM::factory('User', $user_notifications->id);

			$text = 'Здравствуйте, ';
			if ($user->loaded()) {
				$text .= $user->username . '!';
			}
			$text .= ' Вы успешно отписались от всех рассылок. Вы можете снова подписаться на них в личном кабинете - в разделе "Настройки рассылок".';
			return $this->render('pages/message', array('text' => $text));
		}
		return $this->render('pages/message', array(
				'text' => 'Пользователя с таким токеном не существует.',
			)
		);
	}

}
