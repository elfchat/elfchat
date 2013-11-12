<?php
return array(
    'debug' => false,
    'locale' => 'ru',

    'domain' => 'chat.dev',
    'server' => 'http://macbook.local:8080',
    'key' => 'key',

    'database' => 'mysql',
    'mysql' =>
        array(
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'dbname' => 'chat.dev',
            'user' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ),
    'sqlite' =>
        array(
            'driver' => 'pdo_sqlite',
            'user' => NULL,
            'password' => NULL,
            'path' => NULL,
        ),
    'postgres' =>
        array(
            'driver' => 'pdo_pgsql',
            'host' => 'localhost',
            'dbname' => 'elfchat',
            'user' => 'root',
            'password' => NULL,
        ),

    'remember_me' => array(
            'token' => sha1(uniqid()),
        ),
);

 