<?php

class CustomValidation {

	static public function not_equal($value, $not_equal_value, $validation, $field) {
		if ($value != $not_equal_value) {
			return true;
		} else {
			return $validation->error($field, __METHOD__, null);
		}
	}

	static public function has_not_space($string, $validation, $field) {
		if (preg_match('/\s/', $string)) {
			return $validation->error($field, 'has_not_space', null);
		} else {
			return true;
		}
	}

	static public function exist_user_email($value, $field, $validation) {
		$result = (ORM::factory('User')->where('email', '=', $value)->find_all()->count() > 0);
		if ($result) {
			return true;
		} else {
			return $validation->error($field, 'not_exist', null);
		}
	}

	static public function user_is_login($user, $validation) {
		if (($user && $user->has_role(Model_Role::ID_LOGIN)) == true) {
			return true;
		} else {
			return $validation->error('not_enough_rules', null);
		}
	}

}