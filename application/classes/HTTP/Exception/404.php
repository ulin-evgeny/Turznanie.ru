<?php

class HTTP_Exception_404 extends Kohana_HTTP_Exception_404 {

	public function get_response() {
		$path = '/';
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: $path");
		exit();
	}

}
