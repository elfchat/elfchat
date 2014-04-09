<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property int $id
 * @property \ElfChat\Entity\User $user
 * @property string $ip
 * @property int $howLong
 * @property \DateTime $created
 * @property \ElfChat\Entity\User $author
 * @property string $reason
 *
 * @ORM\Entity(repositoryClass="ElfChat\Repository\BanRepository")
 * @ORM\Table("elfchat_ban", indexes={
 *     @ORM\index(name="ip_idx", columns={"ip"}),
 *     @ORM\index(name="user_idx", columns={"user_id"})
 * })
 */
class Ban extends Entity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ElfChat\Entity\User")
     */
    protected $user;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    protected $ip;

    /**
     * How long to bun in seconds.
     * @ORM\Column(type="integer")
     */
    protected $howLong;

    /**
     * @ORM\Column(type="integer")
     */
    protected $created;

    /**
     * @ORM\ManyToOne(targetEntity="ElfChat\Entity\User")
     */
    protected $author;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $reason;

    public function __constructor()
    {
        $this->created = time();
    }

    public function isActive()
    {
        return time() - $this->created < $this->howLong;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return \DateTime::createFromFormat('U', $this->created);
    }

    public function getHowLongString()
    {
        $ch = self::howLongChoices();
        return $ch[$this->howLong];
    }

    static public function howLongChoices()
    {
        return array(
            60 => 'One min',
            60 * 5 => '5 min',
            60 * 15 => '15 min',
            60 * 60 => 'One hour',
            60 * 60 * 12 => '12 hours',
            60 * 60 * 24 => 'One day',
            60 * 60 * 24 * 2 => '2 days',
            60 * 60 * 24 * 7 => '7 days',
            60 * 60 * 24 * 14 => '14 days',
            60 * 60 * 24 * 31 => '31 days',
            -1 => 'Forever',
        );
    }
} 