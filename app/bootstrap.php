<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$loader = require __DIR__ . '/vendor/autoload.php';

ElfChat\Config\LoaderRegistry::setLoader($loader);
Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

define('ELFCHAT_VERSION', '6.0.0');
define('ELFCHAT_EDITION', 'UNLIM');
