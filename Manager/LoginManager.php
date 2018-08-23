<?php

namespace KRG\UserBundle\Manager;

use KRG\UserBundle\Entity\UserInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;

class LoginManager implements LoginManagerInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var UserCheckerInterface */
    private $userChecker;

    /** @var SessionAuthenticationStrategyInterface */
    private $sessionStrategy;

    /** @var RequestStack */
    private $requestStack;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var RememberMeServicesInterface */
    private $rememberMeService;

    /** @var string */
    private $firewallName;

    public function __construct(TokenStorageInterface $tokenStorage,
                                UserCheckerInterface $userChecker,
                                SessionAuthenticationStrategyInterface $sessionStrategy,
                                RequestStack $requestStack,
                                AuthorizationCheckerInterface $authorizationChecker,
                                RememberMeServicesInterface $rememberMeService = null,
                                string $firewallName
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->userChecker = $userChecker;
        $this->sessionStrategy = $sessionStrategy;
        $this->requestStack = $requestStack;
        $this->authorizationChecker = $authorizationChecker;
        $this->rememberMeService = $rememberMeService;
        $this->firewallName = $firewallName;
    }

    final public function logInUser(UserInterface $user, Response $response = null)
    {
        $this->userChecker->checkPreAuth($user);

        $token = $this->createToken($this->firewallName, $user);
        $request = $this->requestStack->getCurrentRequest();

        if (null !== $request) {
            $this->sessionStrategy->onAuthentication($request, $token);

            if (null !== $response && null !== $this->rememberMeService) {
                $this->rememberMeService->loginSuccess($request, $response, $token);
            }
        }

        $this->tokenStorage->setToken($token);
    }

    final public function logOutCurrentUser()
    {
        $this->tokenStorage->setToken(null);
        $this->requestStack->getCurrentRequest()->getSession()->invalidate();
    }

    final public function disconnectIfLogged()
    {
        if ($this->tokenStorage->getToken() && $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->logOutCurrentUser();
        }
    }

    protected function createToken($firewall, UserInterface $user)
    {
        return new UsernamePasswordToken($user, null, $firewall, $user->getRoles());
    }
}
