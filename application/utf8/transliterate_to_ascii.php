<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * UTF8::transliterate_to_ascii
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2011 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _transliterate_to_ascii($str, $case = 0) {
  static $utf8_lower_accents = NULL;
  static $utf8_upper_accents = NULL;

  if ($case <= 0) {
    if ($utf8_lower_accents === NULL) {
      $utf8_lower_accents = array(
          'a' => 'a', 'o' => 'o', 'd' => 'd', '?' => 'f', 'e' => 'e', 's' => 's', 'o' => 'o',
          '?' => 'ss', 'a' => 'a', 'r' => 'r', '?' => 't', 'n' => 'n', 'a' => 'a', 'k' => 'k',
          's' => 's', '?' => 'y', 'n' => 'n', 'l' => 'l', 'h' => 'h', '?' => 'p', 'o' => 'o',
          'u' => 'u', 'e' => 'e', 'e' => 'e', 'c' => 'c', '?' => 'w', 'c' => 'c', 'o' => 'o',
          '?' => 's', 'o' => 'o', 'g' => 'g', 't' => 't', '?' => 's', 'e' => 'e', 'c' => 'c',
          's' => 's', 'i' => 'i', 'u' => 'u', 'c' => 'c', 'e' => 'e', 'w' => 'w', '?' => 't',
          'u' => 'u', 'c' => 'c', 'o' => 'o', 'e' => 'e', 'y' => 'y', 'a' => 'a', 'l' => 'l',
          'u' => 'u', 'u' => 'u', 's' => 's', 'g' => 'g', 'l' => 'l', '?' => 'f', 'z' => 'z',
          '?' => 'w', '?' => 'b', 'a' => 'a', 'i' => 'i', 'i' => 'i', '?' => 'd', 't' => 't',
          'r' => 'r', 'a' => 'a', 'i' => 'i', 'r' => 'r', 'e' => 'e', 'u' => 'u', 'o' => 'o',
          'e' => 'e', 'n' => 'n', 'n' => 'n', 'h' => 'h', 'g' => 'g', 'd' => 'd', 'j' => 'j',
          'y' => 'y', 'u' => 'u', 'u' => 'u', 'u' => 'u', 't' => 't', 'y' => 'y', 'o' => 'o',
          'a' => 'a', 'l' => 'l', '?' => 'w', 'z' => 'z', 'i' => 'i', 'a' => 'a', 'g' => 'g',
          '?' => 'm', 'o' => 'o', 'i' => 'i', 'u' => 'u', 'i' => 'i', 'z' => 'z', 'a' => 'a',
          'u' => 'u', '?' => 'th', '?' => 'dh', '?' => 'ae', 'µ' => 'u', 'e' => 'e', '?' => 'i',
          'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
          'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
          'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
          'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shh', 'ъ' => '?', 'ы' => 'y', 'ь' => '?', 'э' => 'e', 'ю' => 'ju',
          'я' => 'ja',
      );
    }

    $str = str_replace(
            array_keys($utf8_lower_accents), array_values($utf8_lower_accents), $str
    );
  }

  if ($case >= 0) {
    if ($utf8_upper_accents === NULL) {
      $utf8_upper_accents = array(
          'A' => 'A', 'O' => 'O', 'D' => 'D', '?' => 'F', 'E' => 'E', 'S' => 'S', 'O' => 'O',
          'A' => 'A', 'R' => 'R', '?' => 'T', 'N' => 'N', 'A' => 'A', 'K' => 'K', 'E' => 'E',
          'S' => 'S', '?' => 'Y', 'N' => 'N', 'L' => 'L', 'H' => 'H', '?' => 'P', 'O' => 'O',
          'U' => 'U', 'E' => 'E', 'E' => 'E', 'C' => 'C', '?' => 'W', 'C' => 'C', 'O' => 'O',
          '?' => 'S', 'O' => 'O', 'G' => 'G', 'T' => 'T', '?' => 'S', 'E' => 'E', 'C' => 'C',
          'S' => 'S', 'I' => 'I', 'U' => 'U', 'C' => 'C', 'E' => 'E', 'W' => 'W', '?' => 'T',
          'U' => 'U', 'C' => 'C', 'O' => 'O', 'E' => 'E', 'Y' => 'Y', 'A' => 'A', 'L' => 'L',
          'U' => 'U', 'U' => 'U', 'S' => 'S', 'G' => 'G', 'L' => 'L', '?' => 'F', 'Z' => 'Z',
          '?' => 'W', '?' => 'B', 'A' => 'A', 'I' => 'I', 'I' => 'I', '?' => 'D', 'T' => 'T',
          'R' => 'R', 'A' => 'A', 'I' => 'I', 'R' => 'R', 'E' => 'E', 'U' => 'U', 'O' => 'O',
          'E' => 'E', 'N' => 'N', 'N' => 'N', 'H' => 'H', 'G' => 'G', 'D' => 'D', 'J' => 'J',
          'Y' => 'Y', 'U' => 'U', 'U' => 'U', 'U' => 'U', 'T' => 'T', 'Y' => 'Y', 'O' => 'O',
          'A' => 'A', 'L' => 'L', '?' => 'W', 'Z' => 'Z', 'I' => 'I', 'A' => 'A', 'G' => 'G',
          '?' => 'M', 'O' => 'O', 'I' => 'I', 'U' => 'U', 'I' => 'I', 'Z' => 'Z', 'A' => 'A',
          'U' => 'U', '?' => 'Th', '?' => 'Dh', '?' => 'Ae', 'I' => 'I',
          'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
          'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
          'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'Kh', 'Ц' => 'Tc',
          'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch', 'Ъ' => '\'', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Ju',
          'Я' => 'Ja'
      );
    }

    $str = str_replace(
            array_keys($utf8_upper_accents), array_values($utf8_upper_accents), $str
    );
  }

  return $str;
}
