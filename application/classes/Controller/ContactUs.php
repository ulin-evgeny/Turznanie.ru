<?php

class Controller_ContactUs extends Controller {

	public function action_index() {
		if ($_POST) {
			// Валидация
			$validation = Validation::factory($_POST)
				->label('body', 'Текст письма')
				->label('subject', 'Тема письма')
				->rule('subject', 'not_empty')
				->rule('body', 'not_empty');
			if ($this->user->loaded()) {
				$email = $this->user->email;
			} else {
				$email = $_POST['email'];
				$validation
					->rule('email', 'not_empty')
					->rule('email', 'email');
			}

			try {
				$validation->check_with_captcha();
			} catch (ORM_Validation_Exception $e) {
				return $this->render_ajax($e->errors('validation'), Ajax::STATUS_UNSUCCESS);
			} catch (CaptchaException $e) {
				return $this->render_ajax(HelperReCaptcha::render(null), Ajax::STATUS_NEED_CAPTCHA);
			}

			$purifier = HelperHTMLPurifier::get_purifier();
			$subject = $purifier->purify($_POST['subject']);
			$body = $purifier->purify($_POST['body']);

			// Письмо
			$mail = new CustomEmail();
			$mail->FromName = $email;
			$mail->addAddress($GLOBALS['SITE_INFO']['email']);
			$mail->Subject = $subject;
			$mail->Body = $body;
			$mail->send(array(
				'intro' => false,
				'signature' => false,
				'dont_answer' => false
			));

			return $this->render_ajax(new PageMessage(array(
				'text' => 'Сообщение отправлено! Спасибо за уделенное вами время!'
			)));
		}

		return $this->render('pages/contact_us');
	}
}
