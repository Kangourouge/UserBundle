<?php

namespace KRG\UserBundle\Controller;

use KRG\UserBundle\Manager\LoginManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /** @var LoginManagerInterface */
    protected $loginManager;

    /** @var AuthenticationUtils */
    protected $authenticationUtils;

    public function __construct(LoginManagerInterface $loginManager, AuthenticationUtils $authenticationUtils)
    {
        $this->loginManager = $loginManager;
        $this->authenticationUtils = $authenticationUtils;
    }

    /**
     * @Route("/login", name="krg_user_login")
     */
    public function loginAction(Request $request)
    {
        $this->loginManager->disconnectIfLogged();
        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();

        return $this->render('@KRGUser/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    /**
     * @Route("/guess/{token}", name="krg_user_guess_token")
     */
    public function guessAction()
    {
        return new Response();
    }

    /**
     * @Route("/login_check", name="krg_user_login_check")
     */
    public function loginCheckAction()
    {
    }

    /**
     * @Route("/logout", name="krg_user_logout")
     */
    public function logoutAction()
    {
    }

    /**
     * @Route("/restricted", name="krg_user_restricted")
     */
    public function restrictedAction(Request $request)
    {
        return $this->render('@KRGUser/security/restricted.html.twig');
    }
}
