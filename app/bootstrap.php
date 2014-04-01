<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//if (file_exists(__DIR__ . '/open/config.php')) {
//    strpos($_SERVER['REQUEST_URI'], 'install') ? die('Installer does not found.') : header('Location: install/index.php');
//    exit(0);
//}

$loader = require_once __DIR__ . '/vendor/autoload.php';

Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));