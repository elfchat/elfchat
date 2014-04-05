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
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

$app = new ElfChat\Application();
$app->boot();

$chat = 1;
$sessionHandler = new PdoSessionHandler($app->entityManager()->getConnection(), array(
    'db_table' => 'elfchat_session'
));

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new SessionProvider(
                $chat,
                $sessionHandler
            )
        )
    ),
    $config['server']['port'],
    $config['server']['host']
);

$server->run();