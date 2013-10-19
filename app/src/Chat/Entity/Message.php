<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Chat\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @property int $id
 * @property \Chat\Entity\User $user
 * @property \DateTime $datetime
 * @property string $room
 * @property string $text
 *
 * @ORM\Entity(repositoryClass="Chat\Repository\MessageRepository")
 * @ORM\Table("elfchat_message")
 */
class Message implements ExportInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Chat\Entity\User")
     */
    protected $user;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $datetime;

    /**
     * @ORM\Column
     */
    protected $room;

    /**
     * @ORM\Column(type="text")
     */
    protected $text;

    public function __construct()
    {
    }

    public function export()
    {
        return array(
            'id' => $this->id,
            'user' => array(
                'id' => $this->user->getId()
            ),
            'datetime' => $this->datetime->format(\DateTime::ISO8601),
            'room' => $this->room,
            'text' => $this->text,
        );
    }

    public function exportWithUser()
    {
        $export = $this->export();
        $export['user'] = $this->user->export();
        return $export;
    }

    public function __get($name)
    {
        $methodName = 'get' . ucfirst($name);
        return method_exists($this, $methodName) ? $this->{$methodName}() : $this->{$name};
    }

    public function __set($name, $value)
    {
        $methodName = 'set' . ucfirst($name);
        return method_exists($this, $methodName) ? $this->{$methodName}($value) : $this->{$name} = $value;
    }

    public function __isset($name)
    {
        return property_exists($this, $name);
    }

    public function setText($text)
    {
        $this->text = mb_substr($text, 0, 1000, 'UTF-8');
    }
}
