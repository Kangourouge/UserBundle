<?php

namespace KRG\UserBundle\Security\Authenticator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    /** @var RouterInterface */
    protected $router;

    /** @var AuthenticationSuccessHandlerInterface */
    protected $authenticationSuccessHandler;

    public function __construct(RouterInterface $router, AuthenticationSuccessHandlerInterface $authenticationSuccessHandler)
    {
        $this->router = $router;
        $this->authenticationSuccessHandler = $authenticationSuccessHandler;
    }

    /**
     * Called on every request. Return whatever credentials you want,
     * or null to stop authentication.
     */
    public function getCredentials(Request $request)
    {
        // Match the url /guess/{token} via the provider security.providers.invitation_token_provider
        if ($request->get('_route') === 'krg_user_guess_token') {
            return ['token' => $request->attributes->get('token')];
        }

        return null;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = $credentials['token'];

        if (null === $token) {
            return null;
        }

        return $userProvider->loadUserByUsername($token);
    }

    public function createAuthenticatedToken(UserInterface $user, $providerKey)
    {
        return new UsernamePasswordToken($user, null, 'main', $user->getRoles());
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return $this->authenticationSuccessHandler->onAuthenticationSuccess($request, $token);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return null;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->router->generate('krg_user_login'));
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
