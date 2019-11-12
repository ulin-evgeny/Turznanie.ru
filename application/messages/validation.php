<?php

defined('SYSPATH') OR die('No direct script access.');

return array(
	'alpha' => 'Поле :field должно содержать только символы',
	'alpha_dash' => 'Поле :field должно содержать только символы, цифры и дефис',
	'alpha_numeric' => 'Поле :field должно содержать только символы и цифры',
	'color' => 'Поле :field должно быть цветом',
	'credit_card' => 'Поле :field должно быть номером кредитной карты',
	'date' => 'Поле :field должно быть датой',
	'decimal' => 'Поле :field должно быть :param2 значным числом',
	'digit' => 'Поле :field должно быть цифрой',
	'email' => array(
		'default' => 'Поле :field должно быть корректным Email',
		'not_exist' => 'Пользователя с таким Email не существует'
	),
	'email_domain' => 'Поле :field должно содержать email domain',
	'equals' => 'Поле :field должно быть эквивалентно :param2',
	'exact_length' => 'Поле :field должно быть ровно :param2 $$:param2,символ,символа,символов$$ длинной',
	'in_array' => 'Поле :field должно быть в пределах допустимых значений',
	'ip' => 'Поле :field должно быть ip',
	'matches' => ':field и :param3 должны быть одинаковыми',
	'min_length' => 'Поле :field должно быть не меньше :param2 $$:param2,символ,символов,символов$$',
	'max_length' => 'Поле :field не должно превышать :param2 $$:param2,символ,символов,символов$$',
	'not_empty' => 'Необходимо заполнить поле :field',
	'numeric' => 'Поле :field должно состоять из цифр',
	'phone' => 'Некорректно заполнено поле :field',
	'range' => 'Поле :field должно быть в диапазоне от :param2 до :param3',
	'regex' => 'Некорректно заполнено поле :field',
	'url' => 'Поле :field должно быть ссылкой',
	'agree' => array(
		'default' => 'Необходимо согласиться с :field',
	),
	'photo' => array(
		'default' => 'Необходимо загрузить фотографию',
	),
	'file' => array(
		'default' => 'Необходимо загрузить :field',
	),
	'unique' => 'Введенное значение для :field уже существует',
	'not_enough_rules' => Messages::NOT_ENOUGH_RULES
);
