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

// We need to configure application to use common parts.
$app = new ElfChat\Application();
$app->boot();

// Error logger.
ElfChat\Server\ErrorLogger::register($app);

$config = $app->config();
$host = $config->get('server.host', 'macbook.local');
$port = $config->get('server.port', 1337);
$address = '0.0.0.0'; // 0.0.0.0 means receive connections from any

// Init Ratchet server
$loop = LoopFactory::create();
$ratchet = new Ratchet\App($host, $port, $address, $loop);

// Session Integration
$sessionWrapper = function ($component) use ($app) {
    return new SessionProvider(
        $component,
        $app['session.storage.handler'],
        array(
            'name' => 'ELFCHAT',
        )
    );
};

\ElfChat\Server\Controller\Controller::setSaveHandler($app['session.storage.handler']);

// WebSocket Server
$chat = new ElfChat\Server($app);
$wsServer = new WsServer($sessionWrapper($chat));
$ratchet->route('/', $wsServer, array('*'));

// Http services
$memoryUsage = new ElfChat\Server\Controller\MemoryUsage();
$ratchet->route('/memory_usage', $memoryUsage, array('*'));

$ratchet->route('/update_user', new ElfChat\Server\Controller\UpdateUser($chat), array('*'));

// Loops
$loop->addPeriodicTimer(1, function () use ($memoryUsage) {
    $memoryUsage->gather();
});

$ratchet->run();