<?php

/**
 * Class Model_UserAction
 * Этот класс нужен для фиксации каких-либо действий пользователя. Фиксации времени и типа действия (Action). Зачем? Некоторые действия можно выполнять с ограничением по времени. Неизвестно, сколько будет дейстий. Может, спустя время появится еще какие-то (добавится новая фича на сайт). Не создавать же поля в таблице Users.
 * Пример действия - отправка письма для подтверждения Email. Такое письмо можно отправлять раз в несколько минут. В таблице фиксируется тип действия и его время. И конечно пользователь, который произвел это действие.
 */
class Model_UserAction extends ORM {

	protected $_table_name = 'user_actions';

}
