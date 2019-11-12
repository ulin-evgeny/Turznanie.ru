<?php

trait Trait_Tag {

	public function tag_status() {
		if ($_GET) {
			return $this->render_ajax(array('data' => ORM::factory('Tag')->where('title', '=', $_GET['name'])->find()->status));
		}
	}

	public function tag_autocomplete() {
		if ($_GET) {
			$data = [];
			$tags = ORM::factory('Tag')->where('title', 'like', $_GET['starts_with'] . '%');
			if (!CurrentUser::get_user()->is_admin()) {
				$tags = $tags->where('status', '=', '1');
			}
			$tags = $tags->limit($_GET['max_rows'])->find_all()->as_array();

			foreach ($tags as $tag) {
				$data[]['name'] = $tag->title;
			}

			return $this->render_ajax(array('data' => $data));
		}
	}

}
