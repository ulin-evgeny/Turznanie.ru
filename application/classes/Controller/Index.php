<?php

class Controller_Index extends Controller {

	public function action_index() {
		// ---------------------------------
		// Получение item'ов
		// ---------------------------------
		// Установка get params
		$get_params = [];
		$get_params['page'] = 1;
		$get_params['on_page'] = 6;
		$get_params['sort_by'] = Sort::SORT_BY_DATE;
		$get_params['sort_way'] = Sort::SORT_WAY_DESC;

		$is_admin = CurrentUser::get_user()->is_admin();

		// Массив с id всех типов материалов
		$material_type_ids = array(Model_Item::MATERIAL_LITERATURE, Model_Item::MATERIAL_ARTICLE, Model_Item::MATERIAL_NEWS);
		// Получение id дочерних каталогов от каждого типа материала
		$catalog_ids = [];
		foreach ($material_type_ids as $id) {
			$material = ORM::factory('Catalog', $id);
			foreach ($material->get_children(true) as $cat) {
				$catalog_ids[$id][] = $cat->id;
			}
			$query = 'SELECT *
								FROM items
								WHERE catalog_id IN :catalog_ids';
			if (!$is_admin) {
				$query .= ' AND status = ' . Status::STATUS_VISIBLE_VALUE;
			}
			$query .= ' ORDER BY date DESC LIMIT 10';
			$items[$id] = DB::query(Database::SELECT, $query)
				->param(':catalog_ids', $catalog_ids[$id])
				->execute()
				->as_array();
		}
		// ---------------------------------

		$tags_cloud = ColorTags::get_most_used_tags();
		return $this->render('pages/index', array(
			'items' => $items,
			'tags_cloud' => $tags_cloud,
			'is_admin' => $is_admin,
			'billboard_main_img' => 'assets/images/welcome.jpg'
		));
	}

}
