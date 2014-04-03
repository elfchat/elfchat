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

    protected $granted = array();

    protected $authenticated = false;

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
        if (isset($this->roleHierarchy[$role])) {
            $this->granted = $this->roleHierarchy[$role];
        }

        $this->granted = array_merge($this->granted, array($role));

        $this->role = $role;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setAuthenticated($authenticated)
    {
        $this->authenticated = $authenticated;
    }

    public function isAuthenticated()
    {
        return $this->authenticated;
    }

    public function isGranted($role)
    {
        return in_array($role, $this->granted);
    }

    public function isAllowed($path)
    {
        foreach ($this->accessRules as $rule) {
            list($pattern, $role) = $rule;

            if (preg_match("#$pattern#", $path)) {
                return in_array($role, $this->granted);
            }
        }
        return false;
    }
}