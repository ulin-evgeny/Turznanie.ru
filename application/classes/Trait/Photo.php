<?php

trait Trait_Photo {

	public function has_photo() {
		if ($this->photo != '') {
			return true;
		} else {
			return false;
		}
	}
}
