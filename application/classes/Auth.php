<?php

class Auth extends Kohana_Auth {

	protected function _login($username, $password, $remember) {
		return parent::_login($username, $password, $remember);
	}

	public function password($username) {
		return parent::password($username);
	}

	public function check_password($password) {
		return parent::check_password($password);
	}

	public function hash($str) {
		if (!$this->_config['hash_method']) {
			return $str;
		} elseif ($this->_config['hash_method'] == 'md5') {
			return md5($str);
		} elseif ($this->_config['hash_method'] == 'bcrypt') {
			return password_hash($str, PASSWORD_BCRYPT);
		}
		return parent::hash($str);
	}

}
