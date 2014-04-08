<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Server\Controller;

use Guzzle\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServerInterface;
use Ratchet\Session\Storage\VirtualSessionStorage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;

abstract class Controller implements HttpServerInterface
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \SessionHandlerInterface
     */
    static private $saveHandler;

    protected $userId;

    protected $userRole;

    abstract public function action(RequestInterface $request);

    function onOpen(ConnectionInterface $conn, RequestInterface $request = null)
    {
        $this->onRequest($request);

        $userData = $this->session->get('user');

        if (count($userData) == 2 && is_int($userData[0])) {
            $this->userId = $userData[0];
            $this->userRole = $userData[1];

            $response = $this->action($request);
        } else {
            $response = new Response('ACCESS DENIED', Response::HTTP_UNAUTHORIZED);
        }

        $conn->send((string)$response);
        $conn->close();
    }

    protected function onRequest(RequestInterface $request)
    {
        if (null === ($id = $request->getCookie(ini_get('session.name')))) {
            $saveHandler = new NullSessionHandler();
            $id = '';
        } else {
            $saveHandler = self::$saveHandler;
        }

        $serialClass = "Ratchet\\Session\\Serialize\\{$this->toClassCase(ini_get('session.serialize_handler'))}Handler"; // awesome/terrible hack, eh?
        if (!class_exists($serialClass)) {
            throw new \RuntimeException('Unable to parse session serialize handler');
        }
        $serializer = new $serialClass;

        $this->session = new Session(new VirtualSessionStorage($saveHandler, $id, $serializer));

        if (ini_get('session.auto_start')) {
            $this->session->start();
        }
    }

    /**
     * @param string $langDef Input to convert
     * @return string
     */
    protected function toClassCase($langDef)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $langDef)));
    }

    protected function json($data)
    {
        return new Response(json_encode($data), 200, array(
            'Content-Type' => 'application/json; charset=UTF-8',
            'Access-Control-Allow-Origin' => '*'
        ));
    }

    /**
     * @param \SessionHandlerInterface $saveHandler
     */
    public static function setSaveHandler(\SessionHandlerInterface $saveHandler)
    {
        self::$saveHandler = $saveHandler;
    }

    final function onClose(ConnectionInterface $conn)
    {
    }

    final function onMessage(ConnectionInterface $from, $msg)
    {
    }

    final function onError(ConnectionInterface $conn, \Exception $e)
    {
    }
} 