<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Security\Authentication;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use ElfChat\Entity\Ban;
use ElfChat\Entity\User;
use ElfChat\Security\Authentication\Exception\BannedException;
use ElfChat\Security\Authentication\Provider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityMiddleware
{
    protected $provider;

    protected $em;

    protected $remember;

    public function __construct(Provider $provider, EntityManager $em, Remember $remember)
    {
        $this->provider = $provider;
        $this->em = $em;
        $this->remember = $remember;
    }

    public function onRequest(Request $request)
    {
        $session = $request->getSession();

        list($id, $role) = $session->get('user', array(null, 'ROLE_GUEST'));

        if (null === $id && $request->cookies->has(Remember::REMEMBER_ME)) {
            if ($this->remember->check($request->cookies->get(Remember::REMEMBER_ME))) {
                list($id, $role) = $this->remember->getIt();
                $session->set('user', array($id, $role));
            }
        }

        $this->provider->setRole($role);

        if (!$this->provider->isAllowed($request->getPathInfo())) {
            throw new Exception\AccessDeniedException("Access denied to " . $request->getPathInfo());
        }

        if (null !== $id) {
            // Ban check
            $ban = Ban::findActive($id, $request->getClientIp());
            if (!empty($ban)) {
                throw new BannedException($ban[0], Response::HTTP_FORBIDDEN);
            }

            $user = User::find($id);
            if (null !== $user) {
                $this->provider->setUser($user);
                $this->provider->setAuthenticated(true);
            }
        }
    }
}