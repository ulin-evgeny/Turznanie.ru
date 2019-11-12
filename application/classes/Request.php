<?php

class Request extends Kohana_Request {

  public function param($key = NULL, $default = NULL) {
    if (isset($this->_params['controller']) AND ! $this->_params['controller'])
      $this->_params['controller'] = strtolower($this->controller());
    if (isset($this->_params['action']) AND ! $this->_params['action'])
      $this->_params['action'] = strtolower($this->action());

    return parent::param($key, $default);
  }

}
