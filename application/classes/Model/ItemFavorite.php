<?php

class Model_ItemFavorite extends ORM {

  protected $_table_name = 'item_favorites';

  public function is_favored($item_id) {
		$user = CurrentUser::get_user();
  	$favored = ORM::factory('ItemFavorite')->where('user_id', '=', $user->id)->where('item_id', '=', $item_id)->find();
  	return $favored->loaded();
	}

}
