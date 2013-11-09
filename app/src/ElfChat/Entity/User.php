<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ElfChat\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use ElfChat\Validator\Constraints\Unique;

/**
 * @ORM\Entity(repositoryClass="ElfChat\Repository\UserRepository")
 * @ORM\Table("elfchat_user", indexes={
 *     @ORM\index(name="name_idx", columns={"name"})
 * })
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"user" = "ElfChat\Entity\User", "guest" = "ElfChat\Entity\Guest"})
 * @Unique(column="name", groups={"registration"})
 * @Unique(column="email", groups={"registration"})
 */
class User implements ExportInterface
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
    protected $name;

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
     * @ORM\Column(length=255)
     */
    protected $role;

    /**
     * @ORM\OneToOne(targetEntity="ElfChat\Entity\Avatar", cascade={"remove"}, fetch="LAZY")
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

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
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

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($role)
    {
        $this->role = $role;
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

    public function export()
    {
        return array(
            'id' => $this->getId(),
            'name' => $this->getName(),
            'avatar' => (string)$this->getAvatar(),
        );
    }
}
