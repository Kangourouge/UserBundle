<?php

namespace KRG\UserBundle\Controller;

use KRG\UserBundle\Manager\LoginManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="krg_user_login")
     * @Template
     */
    public function loginAction(Request $request)
    {
        $this->container->get(LoginManagerInterface::class)->disconnectIfLogged();
        $authUtils = $this->container->get(AuthenticationUtils::class);
        $error = $authUtils->getLastAuthenticationError();
        $lastUsername = $authUtils->getLastUsername();

        return [
            'last_username' => $lastUsername,
            'error'         => $error,
        ];
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
     * @Route("/restricted/test", name="krg_user_restricted")
     * @Template
     */
    public function restrictedAction(Request $request)
    {
        return [];
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(), [
            '?'.LoginManagerInterface::class,
            '?'.AuthenticationUtils::class
        ]);
    }
}
