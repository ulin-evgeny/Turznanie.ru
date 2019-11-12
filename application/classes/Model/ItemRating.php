<?php

class Model_ItemRating extends ORM {

	protected $_table_name = 'item_rating';

	const RATE_NEGATIVE = -1;
	const RATE_POSITIVE = 1;
	const RATE_INDIFFERENT = 0;

	public function rules() {
		return array(
			'rate' => array(
				array('in_array', array(':value', array(static::RATE_NEGATIVE, static::RATE_INDIFFERENT, static::RATE_POSITIVE))),
			)
		);
	}

}
