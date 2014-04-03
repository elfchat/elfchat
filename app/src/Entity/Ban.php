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
 * @ORM\Entity(repositoryClass="ElfChat\Repository\BanRepository")
 * @ORM\Table("elfchat_ban", indexes={
 *     @ORM\index(name="ip_idx", columns={"ip"}),
 *     @ORM\index(name="user_idx", columns={"user_id"})
 * })
 */
class Ban 
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
     * @ORM\Column(type="datetime")
     * @var \DateTime
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
        $this->created = new \DateTime();
    }

    public function isActive()
    {
        $now = new \DateTime();
        return $now->getTimestamp() - $this->created->getTimestamp() < $this->howLong;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param mixed $reason
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    /**
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $howLong
     */
    public function setHowLong($howLong)
    {
        $this->howLong = $howLong;
    }

    /**
     * @return mixed
     */
    public function getHowLong()
    {
        return $this->howLong;
    }
} 