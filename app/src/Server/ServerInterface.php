<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Server;

interface ServerInterface 
{
    public function send($data);

    public function sendExclude($userId, $data);

    public function sendToUser($userId, $data);

    public function kill($userId);

    public function log($text, $level = 'default');

    public function updateUser();
} 