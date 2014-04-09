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
use ElfChat\Exception\AccessDenied;
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
            throw new AccessDenied("Access denied to " . $request->getPathInfo());
        }

        if (null !== $id) {
            // Ban check
            $ban = $this->em->getRepository('ElfChat\Entity\Ban')->findActive($id, $request->getClientIp());
            if (!empty($ban)) {
                return new Response($this->youAreBanned($ban[0]), Response::HTTP_FORBIDDEN);
            }

            $user = $this->em->getRepository('ElfChat\Entity\User')->find($id);
            if (null !== $user) {
                $this->provider->setUser($user);
                $this->provider->setAuthenticated(true);
            }
        }
    }

    private function youAreBanned(Ban $ban)
    {
        $long = Ban::howLongChoices();
        return <<< HTML
<!doctype html>
<html>
<head>
    <title>You are banned</title>
</head>
<body>
    <h1>You are banned for {$long[$ban->howLong]}</h1>
    <strong>Reason:</strong><br>
    <pre>{$ban->reason}</pre>
</body>
</html>
HTML;

    }
}