<?php

defined('SYSPATH') OR die('No direct script access.');

class ORM extends Kohana_ORM {

	public function get_descendants_common($model_name, $parent_id_field, $levels = 0, $with_self = false, $sort_by = null) {
		$descendants = $this->get_descendants_common_inner($this->id, $model_name, $parent_id_field, $this->id, $levels, $sort_by);
		if ($with_self) {
			$descendants[] = $this;
		}
		return $descendants;
	}

	public function get_descendants_common_inner($start_page_id, $model_name, $parent_id_field, $parent_id, $levels, $sort_by = null) {
		$result_array = array();
		if ($levels) {
			$tmp_page = ORM::factory($model_name, $parent_id);
			$level = 0;
			while ($tmp_page->id != $start_page_id) {
				$tmp_page = ORM::factory($model_name, $tmp_page->parent_id);
				$level++;
			}
			if ($level >= $levels) {
				return [];
			}
		}

		$children = ORM::factory($model_name)->where($parent_id_field, '=', $parent_id)->find_all()->as_array();

		// Сортировка
		if ($sort_by) {
			usort($children, function ($a, $b) use ($sort_by) {
				return strcmp($b->$sort_by, $a->$sort_by);
			});
		}
		// Добавление ребенка в массив и получение детей ребенка (рекурсия)
		foreach ($children as $c) {
			$result_array[] = $c;
			$result_array = array_merge($result_array, $this->get_descendants_common_inner($start_page_id, $model_name, $parent_id_field, $c->id, $levels, $sort_by));
		}
		return $result_array;
	}

	public function has_parent_common($model_name, $parent_id_field, $parent_id, $last_id) {
		if ($parent_id == $last_id) {
			return false;
		}
		$tmp = $this;
		while ($tmp->id != $last_id) {
			$tmp = ORM::factory($model_name)->where('id', '=', $tmp->$parent_id_field)->find();
			if ($tmp->id == $parent_id) {
				return true;
			}
		}
		return false;
	}

	public function get_nesting_level_common($model_name, $parent_id_field, $higher_level_id, $last_id) {
		$tmp = $this;
		if (!$tmp->has_parent_common($model_name, $parent_id_field, $higher_level_id, $last_id)) {
			throw new Exception('Невозможно узнать уровень вложенности, так как текущий элемент отсутствует во вложении элемента с $higher_level_id (или является им)');
		}
		$level = 0;
		while ($tmp->id != $higher_level_id) {
			$tmp = ORM::factory($model_name)->where('id', '=', $tmp->$parent_id_field)->find();
			$level++;
		}
		return $level;
	}

}
