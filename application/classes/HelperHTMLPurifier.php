<?php

class HelperHTMLPurifier {

	static public function get_purifier() {
		$config = HTMLPurifier_Config::createDefault();
		$config->set('HTML.AllowedElements', ALLOWABLE_TAGS);
		$config->set('HTML.AllowedAttributes', ALLOWABLE_ATTRS);

		// С помощью этой строчки htmlpurifier вместо удаления неразрешенных тегов будет преобразовывать их в html сущности. Отключил, так как CKEditor испытывает проблемы с отображением тега script. В итоге, его (тег) невозможно удалить, ведь он не отображается.
		//$config->set('Core.EscapeInvalidTags', true);

		$purifier = new HTMLPurifier($config);
		return $purifier;
	}
}