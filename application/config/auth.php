<?php

defined('SYSPATH') OR die('No direct access allowed.');

return array(
    'driver' => 'ORM',
    'hash_method' => 'bcrypt',
    'hash_key' => 'ENTER YOUR HASH',
    'lifetime' => 31536000,
    'session_key' => 'auth_user'
);
