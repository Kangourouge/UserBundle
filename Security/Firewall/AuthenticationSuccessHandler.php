<?php

namespace KRG\UserBundle\Security\Firewall;

use KRG\UserBundle\Entity\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * Firewalls: guess and secured_area pass here
     *
     * @param Request $request
     * @param TokenInterface $token
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $path = $this->determineTargetUrl($request);

        if (self::hasAdminRole($token->getRoles())) {
            $path = $this->router->generate('sonata_admin_redirect'); // TODO: config.yml
        }

        // TODO: ici pour peut tester si le Profil est complet et rediriger vers l'edition de profil

        return $this->httpUtils->createRedirectResponse($request, $path);
    }

    /**
     * @param array $roles
     *
     * @return bool
     */
    protected static function hasAdminRole(array $roles)
    {
        /* @var $role Role */
        foreach ($roles as $role) {
            if (in_array($role->getRole(), [UserInterface::ROLE_SUPER_ADMIN, UserInterface::ROLE_ADMIN])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param RouterInterface $router
     */
    public function setRouter($router)
    {
        $this->router = $router;
    }

    /**
     * @param ValidatorInterface $validator
     */
    public function setValidator($validator)
    {
        $this->validator = $validator;
    }
}
