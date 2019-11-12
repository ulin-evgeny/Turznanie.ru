<?php

defined('SYSPATH') OR die('No direct access allowed.');

class Model_User extends Model_Auth_User {

	use Trait_Photo;

	const FOLDER = 'users/';
	const PATH_FILES = DOCROOT . FOLDER_FILES . Model_User::FOLDER;
	const PATH_IMAGES = Model_User::PATH_FILES . FOLDER_IMAGES;

	const SEX_MAN = 1;
	const SEX_WOMAN = 2;

	const NOT_SPECIFIED_PLACEHOLDER = 'не указано';

	const USER_IS_ADMIN = 1;
	const USER_IS_OWNER = 2;
	const USER_IS_NOT_ADMIN_OR_OWNER = 3;

	const PHOTO_SIZES = array(
		'sm' => array(
			'width' => 122,
			'height' => 122
		),
		'xs' => array(
			'width' => 60,
			'height' => 60
		)
	);

	const MAX_PHOTO_SIZE = 2 * MB;
	const PHOTO_EXTENSIONS = array(
		IMAGETYPE_JPEG,
		IMAGETYPE_GIF,
		IMAGETYPE_PNG
	);

	const TOKEN_RESTORE_LIFETIME = 60 * 60; // на восстановление пароля дается 60 минут
	const TOKEN_CHANGE_EMAIL_LIFETIME = 60 * 60; // на изменение Email дается 60 минут

	/**
	 * Проверяет - либо пользователь администратор. Либо владелец. Владелец чего? Комментария, публикации - не важно. Лишь бы у модели было поле user_id.
	 * @param $item
	 * @return int
	 */
	public function is_admin_or_owner($item) {
		if ($this->is_admin()) {
			return static::USER_IS_ADMIN;
		}
		if ($item->user_id === $this->id) {
			return static::USER_IS_OWNER;
		}
		return static::USER_IS_NOT_ADMIN_OR_OWNER;
	}

	public function can_edit_item($item) {
		$is_admin_or_owner = $this->is_admin_or_owner($item);
		if ($is_admin_or_owner === static::USER_IS_ADMIN) {
			return true;
		} elseif ($is_admin_or_owner === static::USER_IS_OWNER && strtotime($item->date) + $item::EDITABLE_TIME >= time()) {
			return true;
		} else {
			return false;
		}
	}

	public function can_edit_comment($comment) {
		$is_admin_or_owner = $this->is_admin_or_owner($comment);
		if ($is_admin_or_owner === static::USER_IS_ADMIN) {
			return true;
		} elseif ($is_admin_or_owner === static::USER_IS_OWNER && strtotime($comment->date) + $comment::EDITABLE_TIME >= time()) {
			return true;
		}
		return false;
	}

	public function is_item_author($item) {
		return $item->user_id === $this->id;
	}

	public function is_admin() {
		return $this->has_role(array(Model_Role::ID_SUPERADMIN, Model_Role::ID_ADMIN));
	}

	public function get_url() {
		return '/user/' . $this->username;
	}

	public function get_photo_by_size($size) {
		if ($this->photo != '') {
			return static::PATH_IMAGES . Helper::get_size_folder($size) . '/' . $this->photo;
		} else {
			return static::get_default_photo_by_size_and_sex($size, $this->sex);
		}
	}

	public function has_role($role) {
		if (is_array($role)) {
			return (!empty(array_intersect($role, $this->get_roles())));
		} else {
			return in_array($role, $this->get_roles());
		}
	}

	public function get_roles() {
		return ORM::factory('RolesUser')->where('user_id', '=', $this->id)->find_all()->as_array(null, 'role_id');
	}

	public function is_approved() {
		$role = ORM::factory('Role', Model_Role::ID_APPROVED);
		return $this->has('roles', $role);
	}

	public function get_about() {
		return $this->about ? $this->about : 'Пользователь ничего о себе не написал.';
	}

	public function get_item_rate($item) {
		$rate = ORM::factory('ItemRating')->where('user_id', '=', $this->id)->where('item_id', '=', $item->id)->find();
		if ($rate->loaded()) {
			return $rate->rate;
		}
		return 0;
	}

	// ---------------------------------
	// Статические методы
	// ---------------------------------
	static public function get_default_photo_by_size_and_sex($size, $sex) {
		return static::PATH_IMAGES . Helper::get_size_folder($size) . '/' . static::get_default_photo_by_sex($sex);
	}

	static public function get_default_photo_by_sex($sex) {
		if ($sex == 2) {
			return 'woman.jpg';
		} else {
			return 'man.jpg';
		}
	}

}