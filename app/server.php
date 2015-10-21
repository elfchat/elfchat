<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
require __DIR__ . '/bootstrap.php';

use React\EventLoop\Factory as LoopFactory;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\Session\SessionProvider;
use Ratchet\WebSocket\WsServer;

// Use cli opt
$opt = getopt('', array('host::', 'port::', 'path::'));

// We need to configure application to use common parts.
$app = new ElfChat\Application();
$app->boot();

// Error logger.
ElfChat\Server\WebSocketServer\ErrorLogger::register($app['logger']);

$host = array_key_exists('host', $opt) ? $opt['host'] : 'localhost';
$port = array_key_exists('port', $opt) ? $opt['port'] : '8080';
$path = array_key_exists('path', $opt) ? $opt['path'] : '/';
$address = '0.0.0.0'; // 0.0.0.0 means receive connections from any

// Init Ratchet server
$loop = LoopFactory::create();
$ratchet = new Ratchet\App($host, $port, $address, $loop);

// Session Integration
$sessionWrapper = function ($component) use ($app) {
    return new SessionProvider($component, $app['session.storage.handler'], array('name' => 'ELFCHAT'));
};

// WebSocket Server
$chat = new ElfChat\Server\WebSocketServer($app);
$wsServer = new WsServer($sessionWrapper($chat));
$ratchet->route($path, $wsServer, array('*'));

// WebSocket Controller
$controller = new ElfChat\Server\WebSocketServer\Controller($chat);
$factory = new \ElfChat\Server\WebSocketServer\Controller\ActionFactory(
    $controller,
    $app['session.storage.handler'],
    $app['security.provider']
);

// Http services
$ratchet->route("$path/kill", $factory->create('kill', 'ROLE_MODERATOR'), array('*'));
$ratchet->route("$path/log", $factory->create('log', 'ROLE_MODERATOR'), array('*'));
$ratchet->route("$path/update_user", $factory->create('updateUser', 'ROLE_GUEST'), array('*'));
$ratchet->route("$path/memory_usage", $factory->create('memoryUsage', 'ROLE_ADMIN'), array('*'));

// Loops
$loop->addPeriodicTimer(1, function () use ($app,$controller) {
    // http://stackoverflow.com/a/26791224/1677077
    if ($app['em']->getConnection()->ping() === false) {
        $app['em']->getConnection()->close();
        $app['em']->getConnection()->connect();
    }
    $controller->gatherMemoryUsage();
});

$ratchet->run();
