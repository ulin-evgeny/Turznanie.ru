<?php

defined('SYSPATH') or die('No direct script access.');

return array(
	'password' => array(
		'min_length' => ':field должен быть не меньше :param2 символов',
		'max_length' => ':field не должен превышать :param2 символов',
		'not_empty' => ':field не может быть пустым'
	),
	'password_confirm' => array(
		'matches' => 'Пароли не совпадают'
	),
	'birthday' => array(
		'not_correct' => 'Некорректный формат данных для поля :field. Выберите дату из календаря, а не вводите ее вручную.',
		'not_correct_min_and_max' => 'Некорректно заполнено поле :field'
	),
	'password_old' => array(
		'incorrect' => 'Неправильный старый пароль'
	),
	'email' => array(
		'unique' => 'Данный Email уже существует'
	)
);
