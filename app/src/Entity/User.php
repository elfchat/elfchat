<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ElfChat\Entity;

use Doctrine\ORM\Mapping as ORM;
use ElfChat\Entity\Avatar;
use ElfChat\Entity\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use ElfChat\Validator\Constraints\Unique;

/**
 * @property $id
 * @property string $name
 * @property string $password
 * @property string $email
 * @property string $role
 * @property \ElfChat\Entity\Avatar $avatar
 *
 * @ORM\Entity
 * @ORM\Table("elfchat_user", indexes={
 *     @ORM\Index(name="name_idx", columns={"name"}),
 *     @ORM\Index(name="remote_idx", columns={"remoteSource", "remoteId"}),
 * })
 * @ORM\InheritanceType("SINGLE_TABLE")
 *
 * @Unique(column="name", groups={"registration"})
 * @Unique(column="email", groups={"registration"})
 */
class User extends Entity
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
     * @ORM\OneToOne(targetEntity="ElfChat\Entity\Avatar", cascade={"remove"}, fetch="EAGER")
     * @var Avatar
     */
    protected $avatar;

    public function __construct()
    {
        $this->role = 'ROLE_USER';
    }

    public function export()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => (string)$this->avatar,
        );
    }

    /**
     * @return User[]
     */
    public static function findAllUsers()
    {
        $dql = "SELECT u, a FROM ElfChat\Entity\User u LEFT JOIN u.avatar a WHERE NOT u INSTANCE OF ElfChat\Entity\User\GuestUser";
        $query = self::entityManager()->createQuery($dql);
        return $query->getResult();
    }

    /**
     * @param $a
     * @return array
     */
    public static function queryNames($a)
    {
        $dql = "SELECT u FROM ElfChat\Entity\User u WHERE u.name LIKE ?1";
        $query = self::entityManager()->createQuery($dql);
        $query->setParameter(1, $a . '%');
        $query->setMaxResults(10);
        return $query->getResult();
    }

    /**
     * @param $name
     * @return User
     */
    public static function findOneByName($name)
    {
        $dql = "SELECT u FROM ElfChat\Entity\User u WHERE u.name = ?1 AND u INSTANCE OF ElfChat\Entity\User";
        $query = self::entityManager()->createQuery($dql);
        $query->setParameter(1, $name);
        return $query->getSingleResult();
    }
}
