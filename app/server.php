<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
require_once __DIR__ . '/bootstrap.php';

use Ratchet\ConnectionInterface;
use React\EventLoop\Factory as LoopFactory;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\Session\SessionProvider;
use Ratchet\WebSocket\WsServer;

// We need to configure application to use common parts.
$app = new ElfChat\Application();
$app->boot();

$config = $app->config();
$host = $config->get('server.host', 'macbook.local');
$port = $config->get('server.port', 1337);
$address = '0.0.0.0';

$chat = new ElfChat\Server($app);
$wsServer = new WsServer(
    new SessionProvider(
        $chat,
        $app['session.storage.handler'],
        array(
            'name' => 'ELFCHAT',
        )
    )
);

$loop = LoopFactory::create();
$ratchet = new Ratchet\App($host, $port, $address, $loop);

class Root implements Ratchet\Http\HttpServerInterface
{
    public function onOpen(ConnectionInterface $conn, Guzzle\Http\Message\RequestInterface $request = null)
    {
        $conn->send('<html><head><title>Hello World!</title></head><body><h1>'.memory_get_usage(true).'</body></html>');
        $conn->close();
    }

    function onClose(ConnectionInterface $conn)
    {
    }

    function onMessage(ConnectionInterface $from, $msg)
    {
    }

    function onError(ConnectionInterface $conn, \Exception $e)
    {
    }
}

$ratchet->route('/', $wsServer, array('*'));
$ratchet->route('/memory_usage', new Root(), array('*'));

$loop->addPeriodicTimer(10, function () use ($app, $chat) {
    memory_get_usage(true);
});

$ratchet->run();