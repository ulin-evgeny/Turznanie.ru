<?php

class Model_ItemComment extends ORM {

	protected $_table_name = 'item_comments';
	protected $_belongs_to = array(
		'user' => array(
			'model' => 'User',
			'foreign_key' => 'user_id',
		),
	);

	const EDITABLE_TIME = 60 * 60 * 24; // сутки

	public function rules() {
		return array(
			'text' => array(
				array('not_empty'),
				array('min_length', array(':value', 1))
			),
			'status' => array(
				array('in_array', array(':value', array_keys(Status::STATUSES_HV)))
			)
		);
	}

	public function labels() {
		return array(
			'text' => 'с комментарием' // чтобы в сообщении получилоь "Поле с комментарием" (не может быть пустым, например).
		);
	}

	public function delete() {
		if (!CurrentUser::get_user()->can_edit_comment($this)) {
			$validation = new Validation(array());
			$validation->error(null, 'not_enough_rules');
			throw new ORM_Validation_Exception(null, $validation);
		}

		parent::delete();
	}

	public function update(Validation $validation = NULL) {
		if (!CurrentUser::get_user()->can_edit_comment($this)) {
			$validation = new Validation(array());
			$validation->error(null, 'not_enough_rules');
			throw new ORM_Validation_Exception(null, $validation);
		}

		parent::update($validation);
	}

	public function has_been_edit() {
		return $this->edit_user_id && $this->edit_date;
	}

	public function get_url() {
		$item = ORM::factory('Item', $this->item_id);
		return $item->get_url() . '#comment_' . $this->id;
	}

	public function save_comment($is_edit = false) {
		$item = ORM::factory('Item', $_POST['item_id']);
		$text = $_POST['text'];
		$user = CurrentUser::get_user();
		$this->text = $text;
		$preview = strip_tags($text);

		$mentioned_user_ids = [];
		if (isset($_POST['mentioned_user_usernames'])) {
			$mentioned_user_usernames = $_POST['mentioned_user_usernames'];
			foreach ($mentioned_user_usernames as $mentioned_username) {
				$mentioned_user = ORM::factory('User')->where('username', '=', $mentioned_username)->find();
				if ($mentioned_user->loaded()) {
					$mentioned_user_ids[] = $mentioned_user;
				}
			}
		}

		if (!empty($mentioned_user_ids)) {
			$this->mentioned_user_ids = implode(',', $mentioned_user_ids);
		} else {
			$this->mentioned_user_ids = '';
		}

		if ($is_edit && $this->status == Status::STATUS_VISIBLE_VALUE) {
			$comment_old = ORM::factory('ItemComment', $this->id);
			if ($this->mentioned_user_ids !== $comment_old->mentioned_user_ids) {
				$validation = new Validation(array());
				$validation->error('mentioned_user_ids', 'visible_status');
				throw new ORM_Validation_Exception($this->errors_filename(), $validation);
			}
		}

		$preview_maximum_length = $this->table_columns()['preview']['character_maximum_length'];
		if (mb_strlen($preview) > $preview_maximum_length) {
			$preview = substr($preview, 0, $preview_maximum_length - 3) . '...';
		} else {
			$preview = substr($preview, 0, $preview_maximum_length);
		}
		$this->preview = $preview;
		$this->item_id = $item->id;

		$additional = new Validation(array('user' => $user));
		$additional->rule('user', 'CustomValidation::user_is_login', array(':value', ':validation'));

		if ($is_edit) {
			$this->edit_date = date(MYSQL_DATE_FORMAT);
			$this->edit_user_id = $user->id;
		} else {
			$this->user_id = $user->id;
			$this->status = Status::STATUS_HIDDEN_VALUE;
		}

		$this->check_with_captcha($additional);
		$this->save();
	}

	public function change_status() {
		$old_status = $this->status;
		$this->status = $_POST['status'];
		$notify_item_author = false;
		$notify_mentioned_users = false;

		$notify_comment_author = false;
		if ($this->status != $old_status) {
			$notify_comment_author = true;
		}
		if (intval($this->status) === Status::STATUS_VISIBLE_VALUE) {
			if (!$this->notification_sent_item_author) {
				$this->notification_sent_item_author = true;
				$notify_item_author = true;
			}
			if (!$this->notification_sent_mentioned_users) {
				$this->notification_sent_mentioned_users = true;
				$notify_mentioned_users = true;
			}
		}

		$this->save();

		$url = Helper::get_site_url() . $this->get_url();

		// отправка сообщений
		if ($notify_comment_author) {
			$comment_author = ORM::factory('User', $this->user_id);
			if ($comment_author->loaded() && $comment_author->has_notification('comment_status')) {
				$mail = new CustomEmail();
				$mail->set_subject_and_body('comment_change_status', array('url' => $url, 'status_string' => Status::STATUSES_HV[$this->status]));
				$mail->addAddress($comment_author->email, $comment_author->username);
				$mail->send(array('unsubscribe' => true));
			}
		}
		if ($notify_item_author) {
			$item = ORM::factory('Item', $this->item_id);
			if ($this->user_id != $item->user_id) {
				$item_user = $item->get_user();
				if ($item_user->loaded() && $item_user->has_notification('comment_add')) {
					$mail = new CustomEmail();
					$mail->set_subject_and_body('new_comment', array('url' => $url));
					$mail->addAddress($item_user->email, $item_user->username);
					$mail->send(array('unsubscribe' => true));
				}
			}
		}
		if ($notify_mentioned_users) {
			foreach ($this->get_mentioned_users() as $mentioned_user) {
				if ($mentioned_user->has_notification('mention_in_comment')) {
					$mail = new CustomEmail();
					$mail->set_subject_and_body('mention_in_comment', array('url' => $url));
					$mail->addAddress($mentioned_user->email, $mentioned_user->username);
					$mail->send(array('unsubscribe' => true));
				}
			}
		}
	}

	public function get_mentioned_users() {
		$ids = $this->mentioned_user_ids;
		$users = array();
		if ($ids) {
			$ids = explode(',', $ids);
			foreach ($ids as $id) {
				$user = ORM::factory('User', $id);
				if ($user->loaded()) {
					$users[] = $user;
				}
			}
		}
		return $users;
	}

	public function get_mentioned_usernames() {
		$usernames = array();
		foreach ($this->get_mentioned_users() as $mentioned_user) {
			$usernames[] = $mentioned_user->username;
		}
		return $usernames;
	}

}
