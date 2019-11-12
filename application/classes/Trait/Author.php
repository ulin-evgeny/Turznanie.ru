<?php

trait Trait_Author {

	public function author_autocomplete() {
		if ($_GET) {
			$data = [];
			$authors = ORM::factory('Author')->where('title', 'like', '%' . $_GET['starts_with'] . '%');
			if (!CurrentUser::get_user()->is_admin()) {
				$authors = $authors->where('status', '=', '1');
			}
			$authors = $authors->limit($_GET['max_rows'])->find_all()->as_array();

			foreach ($authors as $author) {
				$data[]['name'] = $author->title;
			}
			return $this->render_ajax(array('data' => $data));
		}
	}

}