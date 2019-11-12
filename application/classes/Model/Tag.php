<?php

class Model_Tag extends ORM {

	protected $_table_name = 'tags';

	public function filters() {
		return array(
			'title' => array(
				array('HelperText::super_trim', array(':value')),
				array('mb_strtolower', array(':value'))
			)
		);
	}

	public function rules() {
		return array(
			'title' => array(
				array('not_empty'),
				array('min_length', array(':value', Search::MIN_SEARCH_LENGTH)),
				array('CustomValidation::has_not_space', array(':value', ':validation', ':field')),
				array(array($this, 'unique'), array('title', ':value'))
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
	// ===========================================================

	public function get_status_label() {
		switch ($this->status) {
			case -1:
				$result = 'label-danger';
				break;
			case 0:
				$result = 'label-plain';
				break;
			case 1:
				$result = 'label-success';
				break;
		}
		return $result;
	}

	public function get_seo_url() {
		return ORM::factory('Seo', Model_Seo::ID_CATALOG_TAGS)->get_url() . '?id=' . $this->id;
	}

	public function get_link_to_search() {
		return ORM::factory('Seo', Model_Seo::ID_SEARCH_TAGS)->get_url() . URL::query(array('text' => $this->title), false);
	}

}
