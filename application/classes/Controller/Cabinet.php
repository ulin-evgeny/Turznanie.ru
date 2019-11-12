<?php

class Controller_Cabinet extends Controller_CabinetCommon {

	public function render($template, $data = array()) {
		$data = array_merge($data, array('header' => 'Личный кабинет'));
		parent::render($template, $data);
	}

	public function action_mailing() {
		if ($_POST) {
			try {
				$user_notifications = ORM::factory('UserNotification', $this->user->id);
				if (!$user_notifications->loaded()) {
					$user_notifications->id = $this->user->id;
				}
				if ($_POST['status'] == 'true') {
					$value = 1;
				} else {
					$value = 0;
				}
				$user_notifications->{$_POST['name']} = $value;
				$user_notifications->save();
			} catch (Exception $e) {
				return $this->render_ajax('Ошибка при сохранении', Ajax::STATUS_UNSUCCESS);
			}
			return $this->render_ajax('ok');
		}

		return $this->render('cabinet/mailing', array(
			'user_notifications' => ORM::factory('UserNotification', $this->user->id)
		));
	}

	public function action_index() {
		if ($_POST) {
			//---------------------------------------------
			// Работа с аватаркой
			//---------------------------------------------
			// Валидация фотки (если она загружена)
			if ($_POST['photo_changed'] == 1 && $_FILES['photo']['size'] != 0) {
				$dont_upload_photo = PhotoSizepackUploader::find_validation_errors(Model_User::PHOTO_EXTENSIONS, Model_User::MAX_PHOTO_SIZE);
				if ($dont_upload_photo) {
					return $this->render_ajax(array('message' => $dont_upload_photo), Ajax::STATUS_UNSUCCESS);
				}
			} else {
				$dont_upload_photo = true;
			}

			if (!$dont_upload_photo || PhotoSizepackUploader::is_deleting($dont_upload_photo) && $this->person->photo != '') {
				$sizes = Model_User::PHOTO_SIZES;
				$path = Model_User::PATH_IMAGES;

				if (!$dont_upload_photo) {
					if ($this->user->photo == '') {
						$photo = PhotoSizepackUploader::save_photo_sizepack($path, $this->person->id . time(), $sizes);
					} else {
						$photo = PhotoSizepackUploader::change_photo_sizepack($path, $this->person->id . time(), $this->person->photo, $sizes);
					}
					$this->user->photo = $photo;
				} elseif (PhotoSizepackUploader::is_deleting($dont_upload_photo) && $this->person->photo != '') {
					// Удаление фотки
					PhotoSizepackUploader::delete_photo_sizepack($path, $this->person->photo, $sizes);
					$this->user->photo = '';
				}
				$this->user->save();
			}
			//---------------------------------------------

			$validation = new Validation($_POST);
			if ($_POST['birthday'] !== Model_User::NOT_SPECIFIED_PLACEHOLDER) {
				$validation->rules('birthday', Model_User::get_birthday_validation());
				$validation->labels($this->user->labels());
			} else {
				$_POST['birthday'] = null;
			}
			$this->user->username = $_POST['username'];
			$this->user->sex = $_POST['sex'];
			$this->user->birthday = $_POST['birthday'];
			$this->user->about = $_POST['about'];

			try {
				$this->user->save($validation);
			} catch (ORM_Validation_Exception $e) {
				return $this->render_ajax($e->errors('models'), Ajax::STATUS_UNSUCCESS);
			}
			return $this->render_ajax('ok');
		}

		return $this->render('cabinet/info_personal', array(
			'max_size' => Model_User::MAX_PHOTO_SIZE,
			'avatar_size' => Model_User::PHOTO_SIZES['sm'],
			'image_types' => PhotoSizepackUploader::get_string_types(Model_User::PHOTO_EXTENSIONS)
		));
	}

	public function action_favorites() {
		$items_ids = ORM::factory('ItemFavorite')->where('user_id', '=', $this->user->id)->find_all()->as_array(null, 'item_id');
		if (!empty($items_ids)) {
			$items = ORM::factory('Item')->where('id', 'in', $items_ids);
			if (!CurrentUser::get_user()->is_admin()) {
				$items->where('status', '=', Status::STATUS_VISIBLE_VALUE);
			}

			$count = $items->reset_and_count();
			// Поскольку в кабинете нет choosing-panel, я строго указываю значения для $sort
			$sort = new Sort(array(
				'seo_id' => $this->cabinet_type,
				'sort_by_default' => Sort::SORT_BY_DATE,
				'sort_by' => Sort::SORT_BY_DATE,
				'sort_way' => Sort::SORT_WAY_DESC
			));
			$pagination = new Pagination(array(
				'count' => $count,
				'page' => isset($_GET['page']) ? $_GET['page'] : null,
				'on_page' => isset($_GET['on_page']) ? $_GET['on_page'] : null
			));
			$items = $items->order_by($sort->get_sort_by(), $sort->get_sort_way());
			$items = $items->limit($pagination->get_on_page());
			$items = $items->offset($pagination->get_offset());
			$items = $items->find_all()->as_array();
		} else {
			$items = ORM::factory('Item')->where('id', '=', -1)->find_all()->as_array();
		}

		$sent_array = [];
		$sent_array['items'] = $items;
		$sent_array['action'] = Cabinet::ACTION_SHOW_VISIBLE;

		if (isset($pagination)) {
			$sent_array['pagination'] = $pagination;
		}

		return $this->render('cabinet/materials', $sent_array);
	}

	public function action_change_password() {
		if ($_POST) {
			try {
				$this->user->change_password(true);
			} catch (ORM_Validation_Exception $e) {
				return $this->render_ajax($e->errors('models'), Ajax::STATUS_UNSUCCESS);
			}

			return $this->render_ajax(new PageMessage(array(
				'text' => 'Пароль успешно изменен!',
				'btn_text' => 'Назад',
				'btn_href' => '/cabinet'
			)));
		}
		return $this->render('cabinet/change_password');
	}

	public function action_change_email() {
		if ($_POST) {
			$new_email = $_POST['new_email'];
			$old_email = $this->user->email;
			if ($new_email === $old_email) {
				return $this->render_ajax('Вы ввели свой текущий Email', Ajax::STATUS_UNSUCCESS);
			}

			// Ключ email, а не new_email - чтобы не создавать в файлах ошибок (например, validation.php) дополнительные сообщения - для new_email.
			$validation = new Validation(array('email' => $_POST['new_email']));

			// Проверка на уникальность поля new_email
			$users_with_same_new_email = ORM::factory('User')->where('new_email', '=', $new_email)->find_all()->as_array();
			foreach ($users_with_same_new_email as $key => $user) {
				if (strtotime($user->token_change_email_time) + Model_User::TOKEN_CHANGE_EMAIL_LIFETIME < time()) {
					$user->new_email = null;
					$user->token_change_email_time = null;
					$user->save();
					unset($users_with_same_new_email[$key]);
				}
			}
			if (count($users_with_same_new_email)) {
				$validation->error('email', 'unique');
			}

			// Проверка на уникальность поля email
			$users_with_same_email = ORM::factory('User')->where('email', '=', $new_email)->find_all()->as_array();
			if (count($users_with_same_email)) {
				$validation->error('email', 'unique');
			}

			$rules = $this->user->rules()['email'];
			$validation->rules('email', $rules);
			$validation->label('email', 'Новый email');

			try {
				$this->user->check($validation);
			} catch (ORM_Validation_Exception $e) {
				return $this->render_ajax($e->errors('models'), Ajax::STATUS_UNSUCCESS);
			}

			// --------------------------------
			// Письмо
			// --------------------------------
			// Токен
			$token = password_hash($this->user->email . $new_email, PASSWORD_BCRYPT);
			$this->user->new_email = $new_email;
			$this->user->token_change_email = $token;
			$this->user->token_change_email_time = date('Y-m-d H:i:s');
			$this->user->save();

			$link = Helper::get_site_url() . '/change_email?token=' . $token;
			$link_active_minutes = Model_User::TOKEN_CHANGE_EMAIL_LIFETIME / 60;
			$link_active_text = 'Ссылка будет активна в течение ' . $link_active_minutes . ' ' . Helper::form_of_word($link_active_minutes, 'минуты', 'минут', 'минут') . '.';

			$mail = new CustomEmail();
			$mail->addAddress($new_email, $this->user->username);
			$mail->set_subject_and_body('new_email', array('link' => $link, 'link_active_text' => $link_active_text));
			$mail->send();

			return $this->render_ajax(new PageMessage(array(
				'text' => 'Почти готово! На новый почтовый ящик отправлено письмо с ссылкой для подтверждения. Пройдите по ней и ваш Email будет изменен. ' . $link_active_text . Messages::CHECK_SPAM_STRING,
				'btn_text' => 'Назад',
				'btn_href' => '/cabinet'
			)));
		}
		return $this->render('cabinet/change_email');
	}

}
