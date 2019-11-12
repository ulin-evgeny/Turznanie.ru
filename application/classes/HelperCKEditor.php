<?php

// ------------------------------------------
// Класс для работы с CKEditor
// ------------------------------------------

class HelperCKEditor {

	const ALLOWABLE_TAGS_IN_BRACKETS = '<p><strong><em><u><s><blockquote><a><sup><sub>';
	const ALLOWABLE_TAGS = '<p><strong><em><u><s><blockquote><a><sup><sub>';

	/**
	 * Рисует textarea со всеми нужными для работы JS параметрами.
	 */
	static public function render($id_and_name, $content = null, $additional_classes = null, $additional_params = null) {
		return '<textarea id="' . $id_and_name . '" name="' . $id_and_name . '" class="ckeditor' . ($additional_classes ? ' ' . $additional_classes : '') . '"' . ($additional_params ? ' ' . $additional_params : '') . '>' . ($content ? $content : '') . '</textarea>';
	}

	static public function tags_remove_brackets_and_add_to_array($tags_string) {
		$result = preg_replace('/>/m', '', $tags_string);
		$result = explode('<', $result);
		unset($result[0]);
		return $result;
	}

	static public function text_length($text) {
		$text = html_entity_decode($text, ENT_QUOTES);
		foreach (static::tags_remove_brackets_and_add_to_array(static::ALLOWABLE_TAGS_IN_BRACKETS) as $tag) {
			$text = HelperText::strip_single_tag($text, $tag);
		}
		$text_length = mb_strlen($text);
		return $text_length;
	}

}

