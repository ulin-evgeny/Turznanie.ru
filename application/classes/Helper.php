<?php

class Helper {

	static public function delete_directory($dir) {
		if (!file_exists($dir)) {
			return true;
		}

		if (!is_dir($dir)) {
			return unlink($dir);
		}

		foreach (scandir($dir) as $item) {
			if ($item == '.' || $item == '..') {
				continue;
			}

			if (!static::delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
				return false;
			}
		}

		return rmdir($dir);
	}

	static public function get_deepest_0($elem) {
		if (is_array($elem)) {
			$elem = reset($elem);
			return static::get_deepest_0($elem);
		} else {
			return $elem;
		}
	}

	static public function get_url_with_params() {
		$url = Request::initial()->url();
		$params = http_build_query(Request::initial()->query());
		$url .= $params ? '?' . $params : '';
		return $url;
	}

	static public function unix_time_to_mysql_time($unix_date) {
		return date(MYSQL_DATE_FORMAT, $unix_date);
	}

	static public function input_date_to_unix_date($input_date) {
		$value = explode('.', $input_date);
		$value_unix = strtotime("$value[2]-$value[1]-$value[0]");
		return $value_unix;
	}

	/**
	 * Проверяет MySQL (формат даты) дату на правильность. Например, чтобы не было 14 месяца, 40 дня и тд.
	 */
	static public function check_mysql_date($date) {
		$tmpDate = explode('-', $date);
		return checkdate($tmpDate[1], $tmpDate[2], $tmpDate[0]);
	}

	static public function insert_part_to_url($url, $position, $part) {
		$exploded_url = explode('/', $url);
		array_splice($exploded_url, $position, 0, $part);
		$result = implode('/', $exploded_url);
		return $result;
	}

	static public function add_params_to_url($url, $params) {
		if (!empty($params)) {
			$result = $url . '?' . http_build_query($params);
			$result = urldecode($result);
		} else {
			$result = $url;
		}
		return $result;
	}

	public static function form_of_word($n, $f1, $f2, $f5) {
		// example Helper::form_of_word($count, 'статья', 'статьи', 'статей');
		$n = abs(intval($n)) % 100;
		if ($n > 10 && $n < 20) {
			return $f5;
		}
		$n = $n % 10;
		if ($n > 1 && $n < 5) {
			return $f2;
		}
		if ($n == 1) {
			return $f1;
		}
		return $f5;
	}

	public static function get_user_ip() {
		if (getenv('REMOTE_ADDR')) {
			$user_ip = getenv('REMOTE_ADDR');
		} elseif (getenv('HTTP_FORWARDED_FOR')) {
			$user_ip = getenv('HTTP_FORWARDED_FOR');
		} elseif (getenv('HTTP_X_FORWARDED_FOR')) {
			$user_ip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif (getenv('HTTP_X_COMING_FROM')) {
			$user_ip = getenv('HTTP_X_COMING_FROM');
		} elseif (getenv('HTTP_VIA')) {
			$user_ip = getenv('HTTP_VIA');
		} elseif (getenv('HTTP_XROXY_CONNECTION')) {
			$user_ip = getenv('HTTP_XROXY_CONNECTION');
		} elseif (getenv('HTTP_CLIENT_IP')) {
			$user_ip = getenv('HTTP_CLIENT_IP');
		}
		$user_ip = trim($user_ip);
		if (empty($user_ip)) {
			return false;
		}
		if (!preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/", $user_ip)) {
			return false;
		}
		return $user_ip;
	}

	public static function divide_items_by_2_groups($items) {
		$items_left = $items_right = array();
		$i = 0;
		$half = ceil(count($items) / 2);
		foreach ($items as $v) {
			if ($i < $half) {
				array_push($items_left, $v);
			} else {
				array_push($items_right, $v);
			}
			$i++;
		}

		return array(
			'left-group' => $items_left,
			'right-group' => $items_right
		);
	}

	/**
	 * Функция преобразует server-константу в client-константу.
	 *
	 * Например, если я напишу вот так:
	 * define('ROOT', $_SERVER["REQUEST_SCHEME"] . '://' . $_SERVER['HTTP_HOST'] . '/');
	 * то при использовании функции на стороне сервера, например, imagejpeg, возникает ошибка:
	 * imagejpeg(http://mysite/files/users/imgs/original/man.jpg): failed to open stream: HTTP wrapper does not support writeable connections
	 *
	 * Если напишу вот так:
	 * define('ROOT', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
	 * То во front-end'e (во view) картинка просто не откроется:
	 * <div style="background-image:url('D:\1Develop\domains\mysite\files/users/imgs/original/man.jpg')"></div>
	 *
	 * @param $const
	 * @return string
	 */
	public static function const_to_client($const) {
		return '/' . str_replace(DOCROOT, '', $const);
	}

	public static function get_site_info_date_range($year) {
		if (date("Y") > $year) {
			return $year . ' - ' . date("Y");
		}
		return $year;
	}

	public static function get_size_folder($size) {
		return $size['width'] . 'x' . $size['height'];
	}

	static public function trim_br($string) {
		$result = preg_replace('/^(<br\s*\/?>)*|(<br\s*\/?>)*$/i', '', $string);
		return $result;
	}

	static public function br_to_space($string) {
		$result = preg_replace('/<br\ ?\/?>/i', ' ', $string);
		return $result;
	}

	static public function remove_elem_from_string($string, $elem, $delimiter = ' ') {
		$elems = explode($delimiter, $string);
		foreach ($elems as $i => $v) {
			if ($v == $elem) {
				$elems = array_slice($elems, $i, 1);
			}
		}
		return implode($delimiter, $elems);
	}

	static public function get_extension($string) {
		$exploded = explode('.', $string);
		return end($exploded);
	}

	/**
	 * На сервер файлы приходят в виде ассоциативного массива с ключами - name, type, size... И в каждом значении данного ассоциативного массива есть данные для каждого файла. $_FILES['name'] содержит три элемента (если от клиента пришло три файла) - имена файлов.
	 * Это не очень удобно, лучше сделать массив файлов. И у каждого файла есть ассоциативный массив в ключами name, type, size, в которых значения этого файла.
	 * Данная функция именно это и делает.
	 * @param $fields_array
	 * @return array
	 */
	static public function get_objects_array_from_fields_array($fields_array) {
		$result = [];
		foreach ($fields_array as $key => $array) {
			foreach ($array as $index => $value) {
				$result[$index][$key] = $value;
			}
		}
		return $result;
	}

	/**
	 * Функция нужна для того, чтобы загрузить страничку и отправить ее клиенту.
	 * Чем не подходит функция file_get_contents? Тем, что клиенту отправляется php код странички, как текст. То есть, не выполняется. Как было <input value="<?= $username ?>">, так и останется.
	 * @param $path
	 * @param array $data
	 * @return string
	 */
	static public function load_page($path, $data = array()) {
		if (!empty($data)) {
			extract($data);
		}
		ob_start();
		include($path);
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	static public function date_to_russian_date($date) {
		return date("d.m.Y H:i", strtotime($date));
	}

	static public function get_site_protocol() {
		if (isset($_SERVER['HTTPS']) &&
			($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
			isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
			$_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
			$protocol = 'https://';
		} else {
			$protocol = 'http://';
		}
		return $protocol;
	}

	public static function get_site_url() {
		$protocol = static::get_site_protocol();
		$domainName = $_SERVER['HTTP_HOST'];
		return $protocol . $domainName;
	}

	static public function transliterate($input) {
		$gost = array(
			"Є" => "YE", "І" => "I", "Ѓ" => "G", "і" => "i", "№" => "-", "є" => "ye", "ѓ" => "g",
			"А" => "A", "Б" => "B", "В" => "V", "Г" => "G", "Д" => "D",
			"Е" => "E", "Ё" => "YO", "Ж" => "ZH",
			"З" => "Z", "И" => "I", "Й" => "J", "К" => "K", "Л" => "L",
			"М" => "M", "Н" => "N", "О" => "O", "П" => "P", "Р" => "R",
			"С" => "S", "Т" => "T", "У" => "U", "Ф" => "F", "Х" => "X",
			"Ц" => "C", "Ч" => "CH", "Ш" => "SH", "Щ" => "SHH", "Ъ" => "'",
			"Ы" => "Y", "Ь" => "", "Э" => "E", "Ю" => "YU", "Я" => "YA",
			"а" => "a", "б" => "b", "в" => "v", "г" => "g", "д" => "d",
			"е" => "e", "ё" => "yo", "ж" => "zh",
			"з" => "z", "и" => "i", "й" => "j", "к" => "k", "л" => "l",
			"м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
			"с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "x",
			"ц" => "c", "ч" => "ch", "ш" => "sh", "щ" => "shh", "ъ" => "",
			"ы" => "y", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya",
			" " => "_", "—" => "_", "," => "_", "!" => "_", "@" => "_",
			"#" => "-", "$" => "", "%" => "", "^" => "", "&" => "", "*" => "",
			"(" => "", ")" => "", "+" => "", "=" => "", ";" => "", ":" => "",
			"'" => "", "\"" => "", "~" => "", "`" => "", "?" => "", "/" => "",
			"\\" => "", "[" => "", "]" => "", "{" => "", "}" => "", "|" => ""
		);
		return strtr($input, $gost);
	}

	static public function split_name_and_ext($filename) {
		$exploded_parts = explode('.', $filename);
		$count_exploded = count($exploded_parts);
		$ext = $exploded_parts[$count_exploded - 1];
		unset($exploded_parts[$count_exploded - 1]);
		$name = implode($exploded_parts);
		return array(
			'name' => $name,
			'ext' => $ext
		);
	}

}
