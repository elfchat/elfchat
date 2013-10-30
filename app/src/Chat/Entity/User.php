<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Chat\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Chat\Validator\Constraints\Unique;

/**
 * @ORM\Entity(repositoryClass="Chat\Repository\UserRepository")
 * @ORM\Table("elfchat_user", indexes={
 *     @ORM\index(name="username_idx", columns={"username"})
 * })
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"user" = "Chat\Entity\User", "guest" = "Chat\Entity\Guest"})
 * @Unique(column="username", groups={"registration"})
 * @Unique(column="email", groups={"registration"})
 */
class User implements UserInterface, ExportInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column
     * @Assert\NotBlank()
     * @Assert\Length(min = "3", max = "20", groups={"registration", "edit"})
     */
    protected $username;

    /**
     * @ORM\Column(nullable=true)
     * @Assert\NotBlank(groups={"registration"})
     * @Assert\Length(min = "4", groups={"registration"})
     */
    protected $password;

    /**
     * @ORM\Column(nullable=true)
     * @Assert\Email()
     */
    protected $email;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $salt;

    /**
     * @ORM\Column(length=255)
     */
    protected $role;

    /**
     * @ORM\OneToOne(targetEntity="Chat\Entity\Avatar", cascade={"remove"}, fetch="LAZY")
     * @var Avatar
     */
    protected $avatar;

    public function __construct()
    {
        $this->role = 'ROLE_USER';
    }

    public function getId()
    {
        return $this->id;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getName()
    {
        return $this->getUsername();
    }

    public function setUsername($name)
    {
        $this->username = $name;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getRoles()
    {
        return array($this->role);
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * @return Avatar
     */
    public function getAvatar()
    {
        return $this->avatar;
    }


    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     *
     * @return void
     */
    public function eraseCredentials()
    {
    }

    public function export()
    {
        return array(
            'id' => $this->getId(),
            'name' => $this->getUsername(),
            'avatar' => (string)$this->getAvatar(),
        );
    }
}
