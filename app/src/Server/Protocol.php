<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Server;

use ElfChat\Entity\Message;
use ElfChat\Entity\User;

class Protocol
{
    const SYNCHRONIZE = 0;

    const USER_JOIN = 1;

    const USER_LEAVE = 2;

    const USER_UPDATE = 3;

    const MESSAGE = 4;

    const PRIVATE_MESSAGE = 5;

    const LOG = 6;

    /**
     * @param int $type
     * @param mixed $data
     * @return array
     */
    public static function data($type, $data)
    {
        return array($type, $data);
    }

    /**
     * @param User $user
     * @return string
     */
    public static function userJoin(User $user)
    {
        return self::data(
            self::USER_JOIN,
            $user->export()
        );
    }

    public static function userLeave(User $user)
    {
        return self::data(
            self::USER_LEAVE,
            $user->export()
        );
    }

    public static function userUpdate(User $user)
    {
        return self::data(
            self::USER_UPDATE,
            $user->export()
        );
    }

    public static function message(Message $message)
    {
        return self::data(
            self::MESSAGE,
            $message->exportWithUser()
        );
    }

    public static function log($text, $level = 'default')
    {
        return self::data(
            self::LOG,
            array(
                'text' => $text,
                'level' => $level,
            )
        );
    }
}