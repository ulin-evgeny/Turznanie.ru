<?php

class Model_Author extends ORM {

	protected $_table_name = 'authors';

	public function rules() {
		return array(
			'title' => array(
				array('not_empty'),
				array('min_length', array(':value', Search::MIN_SEARCH_LENGTH)),
				array(array($this, 'unique'), array('title', ':value')),
				array(function ($value, $validation) {
					$result = preg_match('/^[a-zA-Zа-яА-ЯёЁ0-9_\-\. ]+$/u', $value);
					if (!$result) {
						return $validation->error('title', 'incorrect', null);
					} else {
						return true;
					}
				}, array(':value', ':validation'))
			),
			'status' => array(
				array('in_array', array(':value', array_keys(Status::STATUSES_HV)))
			)
		);
	}

	public function labels() {
		return array(
			'title' => 'Наименование'
		);
	}

	public function filters() {
		return array(
			'title' => array(
				array('HelperText::super_trim', array(':value')),
				array('strip_tags', array(':value'))
			)
		);
	}

	public function get_seo_url() {
		return ORM::factory('Seo', Model_Seo::ID_CATALOG_AUTHORS)->get_url() . '?id=' . $this->id;
	}

	public function get_link_to_search() {
		return ORM::factory('Seo', Model_Seo::ID_SEARCH_AUTHORS)->get_url() . URL::query(array('text' => $this->title), false);
	}
}
