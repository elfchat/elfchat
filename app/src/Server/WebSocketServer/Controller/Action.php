<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Server\WebSocketServer\Controller;

use ElfChat\Security\Authentication\Provider;
use Guzzle\Http\Message\RequestInterface as GuzzleRequest;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServerInterface;
use Ratchet\Session\Storage\VirtualSessionStorage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;

class Action implements HttpServerInterface
{
    /**
     * @var \SessionHandlerInterface
     */
    private $saveHandler;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Provider
     */
    private $securityProvider;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @var string
     */
    private $expectedRole;

    public function __construct($callback, $expectedRole, \SessionHandlerInterface $saveHandler, Provider $securityProvider)
    {
        $this->callback = $callback;
        $this->expectedRole = $expectedRole;
        $this->saveHandler = $saveHandler;
        $this->securityProvider = $securityProvider;
    }

    final function onOpen(ConnectionInterface $conn, GuzzleRequest $request = null)
    {
        $this->onRequest($request);

        $userData = $this->session->get('user');
        if (count($userData) == 2 && is_int($userData[0])) {
            $userId = $userData[0];
            $userRole = $userData[1];
            $this->securityProvider->setRole($userData[1]);


            if ($this->securityProvider->isGranted($this->expectedRole)) {
                $actionRequest = new Request($request, $userId);
                $response = call_user_func($this->callback, $actionRequest);
            } else {
                $response = new Response('ACCESS DENIED', Response::HTTP_FORBIDDEN);
            }

            $conn->send((string)$response);
        }
        $conn->close();
    }

    protected function onRequest(GuzzleRequest $request)
    {
        if (null === ($id = $request->getCookie(ini_get('session.name')))) {
            $saveHandler = new NullSessionHandler();
            $id = '';
        } else {
            $saveHandler = $this->saveHandler;
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