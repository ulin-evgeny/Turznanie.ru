<?php

class Model_ItemTag extends ORM {

	protected $_table_name = 'items_tags';

	public function rules() {
		return array(
			'status' => array(
				array('in_array', array(':value', array_keys(Status::STATUSES_HV)))
			)
		);
	}

}
