<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Security\Authentication;

class Provider
{
    protected $user;

    protected $role;

    protected $roleHierarchy;

    protected $accessRules;

    public function __construct(array $roleHierarchy, array $accessRules)
    {
        $this->accessRules = $accessRules;
        $this->roleHierarchy = $roleHierarchy;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function isGranted($role)
    {
        if (isset($this->roleHierarchy[$this->role])) {
            $granted = $this->roleHierarchy[$this->role];
            return in_array($role, $granted);
        } else {
            return false;
        }
    }
}