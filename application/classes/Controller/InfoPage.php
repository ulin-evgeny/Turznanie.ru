<?php

/**
 * Class Controller_InfoPage
 * Любая страница с простой информацией. То есть, просто информативная страница, на которой ничего не отправляется на сервер. Смотри bootstrap.php.
 */
class Controller_InfoPage extends Controller {

	public function action_index() {
		return $this->render('pages/info_page');
	}

}
