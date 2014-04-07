<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
require_once __DIR__ . '/bootstrap.php';

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\Session\SessionProvider;
use Ratchet\WebSocket\WsServer;

// We need to configure application to use common parts.
$app = new ElfChat\Application();
$app->boot();

$config = $app->config();
$chat = new ElfChat\Server($app);

// Configure Ratchet server with Http, WebSocket and Session support.
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new SessionProvider(
                $chat,
                $app['session.storage.handler'],
                array(
                    'name' => 'ELFCHAT',
                )
            )
        )
    ),
    $config->get('server.port', 1337),
    $config->get('server.host', 'macbook.local')
);

$server->loop->addPeriodicTimer(1, function () use ($app, $chat) {
});

$server->run();