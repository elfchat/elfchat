<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Server;

use ElfChat\Entity\Message;
use ElfChat\Entity\User;

abstract class AbstractServer implements ServerInterface
{
    public function onReceiveData(User $fromUser, $data)
    {
        if ($data[0] === Protocol::MESSAGE && count($data) === 2) {
            return $this->publicMessage($fromUser, $data[1]);
        } else if ($data[0] === Protocol::PRIVATE_MESSAGE && count($data) === 3) {
            return $this->privateMessage($fromUser, $data[1], $data[2]);
        }

        return null;
    }

    protected function publicMessage(User $user, $text)
    {
        $message = new Message();
        $message->user = $user;
        $message->datetime = new \DateTime();
        $message->text = $text;
        $message->save();

        $this->send(Protocol::message($message));

        return $message;
    }

    protected function privateMessage(User $user, $forId, $text)
    {
        $message = new Message();
        $message->user = $user;
        $message->for = User::reference($forId);
        $message->datetime = new \DateTime();
        $message->text = $text;
        $message->save();

        $this->sendToUser($forId, Protocol::message($message));

        return $message;
    }
} 