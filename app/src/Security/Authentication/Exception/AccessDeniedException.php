<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Security\Authentication\Exception;

use Symfony\Component\HttpFoundation\Response;

class AccessDeniedException extends \Exception
{
    public function __construct()
    {
        parent::__construct('', Response::HTTP_FORBIDDEN);
    }
}