<?php

defined('SYSPATH') or die('No direct script access.');

return array(
	'title' => array(
		'min_length' => 'Наименование автора должно быть не меньше :param2 символов',
		'max_length' => 'Наименование автора не должно превышать :param2 символов',
		'not_empty' => 'Необходимо написать наименование автора',
		'unique' => 'Такой автор уже существует',
		'incorrect' => 'Некорректные символы в наименовании автора'
	)
);
