<?php

defined('SYSPATH') OR die('No direct access allowed.');

class Model_Auth_User extends ORM {

	/**
	 * A user has many tokens and roles
	 *
	 * @var array Relationhips
	 */
	protected $_has_many = array(
		'user_tokens' => array('model' => 'User_Token'),
		'roles' => array('model' => 'Role', 'through' => 'roles_users'),
	);

	/**
	 * Rules for the user model. Because the password is _always_ a hash
	 * when it's set, you need to run an additional not_empty rule in your controller
	 * to make sure you didn't hash an empty string. The password rules
	 * should be enforced outside the model or with a model helper method.
	 *
	 * @return array Rules
	 */
	public function rules() {
		return array(
			'email' => array(
				array('not_empty'),
				array(array($this, 'unique'), array('email', ':value')),
				array('email')),
			'username' => array(
				array('not_empty'),
				array('min_length', array(':value', Search::MIN_SEARCH_LENGTH)),
				array(array($this, 'unique'), array('username', ':value')),
				array(function ($value, $validation) {
					$result = preg_match('/^[a-zA-Z0-9_\-]+$/', $value);
					if (!$result) {
						return $validation->error('username', 'incorrect', null);
					} else {
						return true;
					}
				}, array(':value', ':validation')),
			)
		);
	}

	/**
	 * Filters to run when data is set in this model. The password filter
	 * automatically hashes the password when it's set in the model.
	 *
	 * @return array Filters
	 */
	public function filters() {
		return array(
			'password' => array(
				array(array(Auth::instance(), 'hash'))
			),
			'birthday' => array(
				array(__CLASS__ . '::filter_birthday', array(':value'))
			)
		);
	}

	/**
	 * Labels for fields in this model
	 *
	 * @return array Labels
	 */
	public function labels() {
		return array(
			'email' => 'Email',
			'username' => 'Логин',
			'password' => 'Пароль',
			'birthday' => 'Дата рождения',
			'password_confirm' => 'Пароль (еще раз)',
			'about' => 'О себе'
		);
	}

	// -----------------------------------------------------------
	// Функции валидации
	// -----------------------------------------------------------
	/**
	 * Валидация даты рождения на минимум и максимум. Применяется после validate_birthday_input.
	 * @param $value
	 * @param $validation
	 * @return bool
	 */
	static public function validate_birthday_min_and_max($value, $field, $validation) {
		$value_unix = strtotime($value);
		$current_unix = strtotime('today midnight');
		$current_time = date('d-m-Y', $current_unix);
		$current_time_exploded = explode('-', $current_time);
		$current_year = $current_time_exploded[2];
		$min_year = $current_year - 100;
		$min_time_unix = strtotime("$min_year-$current_time_exploded[1]-$current_time_exploded[0]");

		if ($value_unix < $min_time_unix || $value_unix > $current_unix) {
			return $validation->error($field, 'not_correct_min_and_max');
		}

		return true;
	}

	/**
	 * Валидация даты рождения на корректность ввода даты рождения (чтобы соответствовал регулярке).
	 * @param $date
	 * @param $validation
	 * @return bool
	 */
	static public function validate_birthday_input($date, $field, $validation) {
		if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $date)) {
			$tmpDate = explode('.', $date);
			if (checkdate($tmpDate[1], $tmpDate[0], $tmpDate[2])) {
				return true;
			}
		}
		return $validation->error($field, 'not_correct', null);
	}

	static public function validate_password_old($old_password, $new_password, $field, $validation) {
		// Важный момент. Мы не используем готовую функцию check_password из Auth, так как она не загружает из БД пользователя, а берет текущего (Auth::instance()->get_user()). С УЖЕ измененным паролем. И сравнивает поле "Старый пароль" с "Новый пароль". По этой же причине мы сюда отправляем $user_password. Потому что Auth::instance()->get_user()->password вернет новый пароль, а не тот, который там был.
		$config = Kohana::$config->load('auth');
		$is_bcrypt = $config['hash_method'] === 'bcrypt';
		if ($is_bcrypt) {
			$verify = password_verify($new_password, $old_password);
		} else {
			$verify = Auth::instance()->hash($new_password) === $old_password;
		}

		if (!$verify) {
			return $validation->error($field, 'incorrect', null);
		} else {
			return true;
		}
	}

	// -----------------------------------------------------------
	// Функции фильтров
	// -----------------------------------------------------------
	static public function filter_birthday($value) {
		if ($value) {
			$value = Helper::input_date_to_unix_date($value);
			$value = Helper::unix_time_to_mysql_time($value);
		}
		return $value;
	}

	// -----------------------------------------------------------
	// Дополнительные правила
	// -----------------------------------------------------------
	static public function get_password_confirm_rules() {
		return array(
			array('matches', array(':validation', 'password_confirm', 'password')),
			array('not_empty')
		);
	}

	static public function get_password_rules() {
		return array(
			array('max_length', array(':value', 32)),
			array('min_length', array(':value', 6)),
			array('not_empty')
		);
	}

	/**
	 * Complete the login for a user by incrementing the logins and saving login timestamp
	 *
	 * @return void
	 */
	public function complete_login() {
		if ($this->_loaded) {
			// Update the number of logins
			/*
			Нельзя использовать Database_Expression, так как тогда "провалится" валидация на min_value и max_value.
			Например:
			$this->logins = new Database_Expression('logins + 1');
			! Так делать нельзя.
			*/
			$this->logins = $this->logins + 1;

			// Set the last login date
			$this->last_login = time();

			// Save the user
			$this->update();
		}
	}

	/**
	 * Tests if a unique key value exists in the database.
	 *
	 * @param   mixed    the value to test
	 * @param   string   field name
	 * @return  boolean
	 */
	public function unique_key_exists($value, $field = NULL) {
		if ($field === NULL) {
			// Automatically determine field by looking at the value
			$field = $this->unique_key($value);
		}

		return (bool) DB::select(array(DB::expr('COUNT(*)'), 'total_count'))
			->from($this->_table_name)
			->where($field, '=', $value)
			->where($this->_primary_key, '!=', $this->pk())
			->execute($this->_db)
			->get('total_count');
	}

	/**
	 * Allows a model use both email and username as unique identifiers for login
	 *
	 * @param   string  unique value
	 * @return  string  field name
	 */
	public function unique_key($value) {
		return Valid::email($value) ? 'email' : 'username';
	}

	public static function get_birthday_validation() {
		return array(
			array(__CLASS__ . '::validate_birthday_input', array(':value', ':field', ':validation')),
			array(__CLASS__ . '::validate_birthday_min_and_max', array(':value', ':field', ':validation')),
		);
	}

	/**
	 * Используется при регистрации
	 *
	 * Example usage:
	 * ~~~
	 * $user = ORM::factory('User')->create_user($_POST, array(
	 *  'username',
	 *  'password',
	 *  'email',
	 * );
	 * ~~~
	 *
	 * @param array $values
	 * @param array $expected
	 * @throws ORM_Validation_Exception
	 */
	public function create_user($values, $expected) {
		$extra_validation = new Validation($values);
		$extra_validation->rules('password', static::get_password_rules());
		return $this->values($values, $expected)->create($extra_validation);
	}

	/**
	 * Update an existing user
	 *
	 * [!!] We make the assumption that if a user does not supply a password, that they do not wish to update their password.
	 *
	 * Example usage:
	 * ~~~
	 * $user = ORM::factory('User')
	 *  ->where('username', '=', 'kiall')
	 *  ->find()
	 *  ->update_user($_POST, array(
	 *    'username',
	 *    'password',
	 *    'email',
	 *  );
	 * ~~~
	 *
	 * @param array $values
	 * @param array $expected
	 * @throws ORM_Validation_Exception
	 */
	public function update_user($values, $expected = NULL) {
		if (empty($values['password'])) {
			unset($values['password'], $values['password_confirm']);
		}

		// Validation for passwords
		$extra_validation = Model_User::get_password_validation($values);

		return $this->values($values, $expected)->update($extra_validation);
	}

	public function change_password($with_old_password = false) {
		$validation = new Validation($_POST);

		if ($with_old_password) {
			$validation->rule('password_old', __CLASS__ . '::validate_password_old', array($this->password, ':value', ':field', ':validation'));
			$validation->label('password_old', 'Старый пароль');
		}

		$validation->rules('password', static::get_password_rules()); // правила для запрета пустого пароля
		$validation->label('password', 'Новый пароль');
		$validation->rules('password_confirm', static::get_password_confirm_rules()); // правила для подтверждения пароля
		$validation->label('password_confirm', 'Новый пароль еще раз');

		$this->password = $_POST['password'];
		$this->update($validation);
	}

	public function get_token_approve_url() {
		$page = ORM::factory('Seo', Model_Seo::ID_APPROVE);
		return Helper::get_site_url() . $page->get_url() . '?token=' . $this->token_approve;
	}

	public function get_birthday() {
		return $this->birthday ? date('d.m.Y', strtotime($this->birthday)) : Model_User::NOT_SPECIFIED_PLACEHOLDER;
	}

	public function get_token_unsubscribe() {
		$user_notifications = ORM::factory('UserNotification', $this->id);
		return $user_notifications->token_unsubscribe;
	}

	public function get_link_unsubscribe() {
		$seo_page = ORM::factory('Seo', Model_Seo::ID_UNSUBSCRIBE);
		$result = Helper::get_site_url() . $seo_page->get_url() . '?token=' . $this->get_token_unsubscribe();
		return $result;
	}

	public function has_notification($notification) {
		$user_notifications = ORM::factory('UserNotification', $this->id);
		return $user_notifications->$notification;
	}

}