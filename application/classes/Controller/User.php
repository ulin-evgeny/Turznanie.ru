<?php

class Controller_User extends Controller_CabinetCommon {

	public function before() {
		parent::before();
		$seo_page = Model_Seo::get_page_by_url($this->request->url());
		$this->seo_data = Model_Seo::get_seo_data_by_page($seo_page);
		$this->seo_data['title'] = $this->seo_data['title'] . ' пользователя ' . $this->person->username;
	}

	public function render($template, $data = array()) {
		$data = array_merge($data, array('header' => 'Пользователь ' . $this->person->username));
		parent::render($template, $data);
	}

	public function action_index() {
		// default page
		return $this->render('cabinet/info_user');
	}

}