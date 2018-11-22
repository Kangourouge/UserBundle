<?php

namespace KRG\UserBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    use TargetPathTrait;

    /** @var RouterInterface */
    protected $router;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var string */
    protected $adminTargetRoute;

    public function __construct(HttpUtils $httpUtils, array $options = [], RouterInterface $router, ValidatorInterface $validator)
    {
        parent::__construct($httpUtils, $options);

        $this->router = $router;
        $this->validator = $validator;
    }

    /**
     * Firewalls: guess and secured_area pass here
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if ($this->adminTargetRoute && $token->getUser()->isAdmin()) {
            $this->setDefaultTargetPath($this->adminTargetRoute);
        }

        return $this->httpUtils->createRedirectResponse($request, $this->determineTargetUrl($request));
    }

    /**
     * Builds the target URL according to the defined options.
     */
    protected function determineTargetUrl(Request $request)
    {
        if ($targetRoute = $this->getTargetPath($request->getSession(), $this->providerKey)) {
            return $targetRoute;
        }

        return parent::determineTargetUrl($request);
    }

    protected function setDefaultTargetPath(string $targetPath)
    {
        $this->setOptions(array_merge($this->getOptions(), [
            'default_target_path'            => $targetPath,
            'always_use_default_target_path' => true,
        ]));
    }

    public function setAdminTargetRoute(string $adminTargetRoute)
    {
        $this->adminTargetRoute = $adminTargetRoute;
    }

    public function setUserTargetRoute(string $userTargetRoute)
    {
        $this->setDefaultTargetPath($userTargetRoute);
    }
}
