<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\EventListener;

use ElfChat\Entity\File;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class FileSubscriber implements EventSubscriberInterface
{
    private $uploadUrl;

    public function __construct($uploadUrl)
    {
        $this->uploadUrl = $uploadUrl;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        File::setBaseUrl($request->getBasePath() . $this->uploadUrl);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 64)),
        );
    }
}