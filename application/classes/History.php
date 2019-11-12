<?php

class History {

  public static $limit = 10;

  public static function count() {
    return count(json_decode(Cookie::get('item_history'), true));
  }

  public static function get() {
    $history = json_decode(Cookie::get('item_history'), true);

    if (!$history)
      $history = array();

    return $history;
  }

  public static function get_items() {
    return ORM::factory('Item')->where('id', 'IN', History::get())->find_all();
  }

  public static function set($item_id) {
    $history = json_decode(Cookie::get('item_history'), true);

    if (!$history)
      $history = array();

    $key = array_search($item_id, $history);
    if ($key !== false)
      unset($history[$key]);

    array_unshift($history, $item_id);
    $history = array_slice($history, 0, History::$limit);

    Cookie::set('item_history', json_encode($history));
  }

}
