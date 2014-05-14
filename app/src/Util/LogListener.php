<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Util;

use ElfChat\Security\Authentication\Exception\AccessDeniedException;
use ElfChat\Security\Authentication\Exception\BannedException;
use Silex\EventListener\LogListener as BaseLogListener;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LogListener extends BaseLogListener
{
    protected function logException(\Exception $e)
    {
        if ($e instanceof NotFoundHttpException) {
            return;
        }

        if ($e instanceof AccessDeniedException) {
            return;
        }

        if ($e instanceof BannedException) {
            return;
        }

        parent::logException($e);
    }
} 