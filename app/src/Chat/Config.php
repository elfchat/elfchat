<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chat;

use Chat\Config\ConfigInterface;

class Config implements ConfigInterface
{
    public $domain = '';

    public $server = 'http://localhost:8080';

    public $key = '';

    public $database = 'mysql';

    public $mysql = array(
        'driver' => 'pdo_mysql',
        'host' => 'localhost',
        'dbname' => 'elfchat',
        'user' => 'root',
        'password' => '',
        'charset' => 'utf8',

    );

    public $sqlite = array(
        'driver' => 'pdo_sqlite',
        'user' => '',
        'password' => '',
        'path' => '',
    );

    public $postgres = array(
        'driver' => 'pdo_pgsql',
        'host' => 'localhost',
        'dbname' => 'elfchat',
        'user' => 'root',
        'password' => '',
    );

    public $debug = true;

    public $locale = 'ru';
}