<?php

namespace KRG\UserBundle\Security\Firewall;

use KRG\UserBundle\Entity\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\ParameterBagUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    use TargetPathTrait;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var string
     */
    protected $adminRedirectRoute;

    public function __construct(HttpUtils $httpUtils, array $options = array(), RouterInterface $router, ValidatorInterface $validator)
    {
        parent::__construct($httpUtils, $options);

        $this->router = $router;
        $this->validator = $validator;
    }

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

        if (self::hasAdminRole($token->getRoles()) && $this->adminRedirectRoute) {
            $path = $this->router->generate($this->adminRedirectRoute);
        }

        // TODO: ici pour peut tester si le Profil est complet et rediriger vers l'edition de profil

        return $this->httpUtils->createRedirectResponse($request, $path);
    }

    /**
     * Builds the target URL according to the defined options.
     *
     * @return string
     */
    protected function determineTargetUrl(Request $request)
    {
        $session = $request->getSession();
        $key = sprintf('_security.%s.target_path', $this->providerKey);
        if ($session->has($key)) {
            return $session->get($key);
        }

        return parent::determineTargetUrl($request);
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

    public function setAdminRedirectRoute($adminRedirectRoute)
    {
        $this->adminRedirectRoute = $adminRedirectRoute;
    }
}
