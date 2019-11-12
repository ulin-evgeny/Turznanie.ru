<?php

class Model_Catalog extends ORM {

	public function rules() {
		return array(
			'status' => array(
				array('in_array', array(':value', array_keys(Status::STATUSES_HV)))
			)
		);
	}

	public function find_descendant_with_alias($alias) {
		$children = ORM::factory('Catalog')->where('parent_id', '=', $this->id)->find_all()->as_array();
		if (count($children)) {
			foreach ($children as $child) {
				if ($child->alias == $alias) {
					break;
				}
				$child->find_descendant_with_alias($alias);
			}
		}
		if ($child && $child->loaded()) {
			return $child;
		} else {
			return false;
		}
	}

	public function get_url() {
		$catalog = $this;
		$url = '';
		while ($catalog->parent_id != 0) {
			$url = '/' . $catalog->alias . $url;
			$catalog = ORM::factory('Catalog', $catalog->parent_id);
		}
		$url = '/' . $catalog->alias . $url;
		return $url;
	}

	private function get_children_inner($category_id, $result_array) {
		$children = ORM::factory('Catalog')->where('parent_id', '=', $category_id)->find_all()->as_array();
		if (count($children) > 0) {
			foreach ($children as $c) {
				$result_array[] = $c;
				$result_array += $this->get_children_inner($c->id, $result_array);
			}
		}
		return $result_array;
	}

	public function get_children($with_self = false) {
		$all_children = $this->get_children_inner($this->id, array());
		if ($with_self) {
			$all_children[] = $this;
		}
		return $all_children;
	}

	private function get_material_catalog_inner($catalog) {
		if ($catalog->parent_id != 0) {
			$ctg = ORM::factory('Catalog', $catalog->parent_id);
			return $this->get_material_catalog_inner($ctg);
		} else {
			return $catalog;
		}
	}

	public function get_material_catalog() {
		return $this->get_material_catalog_inner(ORM::factory('Catalog', $this->id));
	}

	public function get_parents() {
		$parents = [];
		$parent = $this;
		if ($parent->parent_id != 0) {
			do {
				$parent = ORM::factory('Catalog', $parent->parent_id);
				$parents[] = $parent;
			} while ($parent->parent_id != 0);
		}
		return $parents;
	}

	public function get_descendants($with_self = faLse, $sort_by = null) {
		return $this->get_descendants_common('Catalog', 'parent_id', $with_self, $sort_by);
	}

	static public function get_catalog_id_by_url($url) {
		$alias_literature = ORM::factory('Catalog', Model_Item::MATERIAL_LITERATURE)->alias;
		$alias_article = ORM::factory('Catalog', Model_Item::MATERIAL_ARTICLE)->alias;
		$alias_news = ORM::factory('Catalog', Model_Item::MATERIAL_NEWS)->alias;

		if ($url != '/') {
			$url_parts = explode('/', $url);
			if (!in_array($url_parts[1], array($alias_literature, $alias_article, $alias_news))) {
				$url = '/';
			}
		}

		if ($url != '/') {
			$catalogs = ORM::factory('Catalog')->where('alias', '=', $url_parts[1])->find_all()->as_array();
			if (substr($url, 0, 1) === '/') {
				unset($url_parts[0]);
			}

			foreach ($url_parts as $url_part) {
				foreach ($catalogs as $catalog) {
					$new_catalogs = ORM::factory('Catalog')
						->where('parent_id', '=', $catalog->id)
						->where('alias', '=', $url_part)
						->find_all()
						->as_array();
				}
				if (count($new_catalogs) > 0) {
					$catalogs = $new_catalogs;
				}
			}

			$exception_many_pages = new Exception('Найдено больше одного каталога с построенным url ("' . $url . '") и одинаковым alias.');
			if (count($catalogs) > 1) {
				throw $exception_many_pages;
			} elseif (count($catalogs) == 1) {
				$parent = $catalogs[0];
			} elseif (count($catalogs) == 0) {
				// это нужно, так как дальше обычно идет проверка на loaded.
				$parent = ORM::factory('Catalog');
			}
		} else {
			$parent = ORM::factory('Catalog');
		}

		if ($parent->loaded()) {
			$children = ORM::factory('Catalog')->where('parent_id', '=', $parent->id)->find_all()->as_array();
			if (count($children) == 0) {
				return $parent->parent_id;
			} else {
				return $parent->id;
			}
		} else {
			return 0;
		}

	}

}