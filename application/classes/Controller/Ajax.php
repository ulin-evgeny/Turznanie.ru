<?php

class Controller_Ajax extends Controller {

	public function action_login() {
		if ($this->user->loaded()) {
			if ($this->request->is_ajax()) {
				return $this->render_ajax(null, Ajax::STATUS_UNSUCCESS);
			} else {
				return $this->go_back();
			}
		}
		if (!$this->request->is_ajax()) {
			return $this->custom_redirect("/users/login");
		}
		$output = Helper::load_page(VIEWSPATH . 'pages/login' . '.php', array('context' => $_GET['context']));
		return $this->render_ajax($output);
	}

}
