<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Server\WebSocketServer\Controller;

use ElfChat\Security\Authentication\Provider;
use ElfChat\Server\WebSocketServer\Controller;

class ActionFactory
{
    /**
     * @var \SessionHandlerInterface
     */
    private $saveHandler;

    /**
     * @var Provider
     */
    private $securityProvider;

    /**
     * @var Controller
     */
    private $controller;

    public function __construct(Controller $controller, \SessionHandlerInterface $saveHandler, Provider $securityProvider)
    {
        $this->controller = $controller;
        $this->saveHandler = $saveHandler;
        $this->securityProvider = $securityProvider;
    }

    public function create($method, $role)
    {
        return new Action(
            array($this->controller, $method),
            $role,
            $this->saveHandler,
            $this->securityProvider
        );
    }
} 