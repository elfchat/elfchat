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

$runable = function () {
    // We need to configure application to use common parts.
    $app = new ElfChat\Application();

    // Error logger.
    ElfChat\Server\WebSocketServer\ErrorLogger::register($app['logger']);

    $config = $app->config();
    $host = $config->get('server.host', 'macbook.local');
    $port = $config->get('server.port', 1337);
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
    $ratchet->route('/', $wsServer, array('*'));

    // WebSocket Controller
    $controller = new ElfChat\Server\WebSocketServer\Controller($chat);
    $factory = new \ElfChat\Server\WebSocketServer\Controller\ActionFactory(
        $controller,
        $app['session.storage.handler'],
        $app['security.provider']
    );

    // Http services
    $ratchet->route('/kill', $factory->create('kill', 'ROLE_MODERATOR'), array('*'));
    $ratchet->route('/log', $factory->create('log', 'ROLE_MODERATOR'), array('*'));
    $ratchet->route('/update_user', $factory->create('updateUser', 'ROLE_GUEST'), array('*'));
    $ratchet->route('/memory_usage', $factory->create('memoryUsage', 'ROLE_ADMIN'), array('*'));

    // Loops
    $loop->addPeriodicTimer(1, function () use ($controller) {
        $controller->gatherMemoryUsage();
    });

    $ratchet->run();
};

if (extension_loaded('pcntl')) {
    GracefulDeath::around($runable)
        ->reanimationPolicy(function () {
            return true;
        })
        ->run();
} else {
    $runable();
}