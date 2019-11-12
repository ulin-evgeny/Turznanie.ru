<?php

class CurrentUser {

	static public function get_user() {
		return Auth::instance()->get_user(ORM::factory('User'));
	}

}