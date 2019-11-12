<?php

class HelperText {

	const ALLOWABLE_BR_INFINITELY = -1;

	static public function get_dom_node_inner_html($dom, $dom_node) {
		$result = '';
		foreach ($dom_node->childNodes as $child) {
			$result .= $dom->saveHTML($child);
		}
		return $result;
	}

	static public function create_dom($string = null) {
		$dom = new DOMDocument();
		// ----------------------------------------------------------------
		// Делается обертка в виде <div> для $string. Это нужно, так как из-за LIBXML_HTML_NOIMPLIED теги могут выводиться несколько некорректно. Подробности по ссылке:
		// https://stackoverflow.com/questions/29493678/loadhtml-libxml-html-noimplied-on-an-html-fragment-generates-incorrect-tags
		// Также добавляется кодировка - так как без вместо текста, полученного от keditor, будут кракозябры.
		// ----------------------------------------------------------------
		libxml_use_internal_errors(true); // отключаем вывод ошибок по поводу состава DOM: https://stackoverflow.com/questions/11819603/dom-loadhtml-doesnt-work-properly-on-a-server
		$dom->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . '<div>' . $string . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		$container = $dom->getElementsByTagName('div')->item(0);
		$container = $container->parentNode->removeChild($container);
		while ($dom->firstChild) {
			$dom->removeChild($dom->firstChild);
		}
		while ($container->firstChild) {
			$dom->appendChild($container->firstChild);
		}

		return $dom;
	}

	static public function strip_single_tag($string, $tag) {
		$string1 = preg_replace('/<\/' . $tag . '>/i', '', $string);
		if ($string1 != $string) {
			$string = preg_replace('/<' . $tag . '[^>]*>/i', '', $string1);
		}
		return $string;
	}

	static public function restore_single_tag($string, $tag) {
		$string1 = preg_replace('/(&lt;)\/' . $tag . '(&gt;)/i', '', $string);
		if ($string1 != $string) {
			$string = preg_replace('/(&lt;)' . $tag . '[^(&gt;)]*(&gt;)/i', '', $string1);
		}
		return $string;
	}

	// Находит в строке вместе стоящие теги <br>, количество которых больше, чем $allowable_br, и заменяет их на один.
	static public function crop_multiple_br($string, $allowable_br) {
		if ($allowable_br > 0) {
			$string = preg_replace('/(<br\ ?\/?>\s*){' . ($allowable_br + 1) . ',}/i', str_repeat('<br />', $allowable_br), $string);
		} else {
			$string = preg_replace('/(<br\ ?\/?>\s*){1,}/i', ' ', $string);
		}
		return $string;
	}

	// Удаление множественных пробелов, табуляций и тд.
	static public function multiple_space_to($string, $to = ' ') {
		// здесь также есть 160 пробел (&#160;). CKEditor иногда вместо обычных пробелов (32) вставляет 160. И это проблема - \t и \s их не убирают.
		$string = preg_replace('/[  \t]{1,}/m', $to, $string);
		return $string;
	}

	static public function super_trim($string) {
		// удаление табуляции
		$result = preg_replace("/\t+/", '', $string);
		// обычный trim
		$result = trim($result);
		// удаление zero-width space
		$result = preg_replace('/[\x{200B}-\x{200D}]/u', '', $result);
		return $result;
	}
}