<?php

namespace KRG\UserBundle\Controller;

use KRG\UserBundle\Form\Type\LoginType;
use KRG\UserBundle\Manager\LoginManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /** @var LoginManagerInterface */
    protected $loginManager;

    /** @var AuthenticationUtils */
    protected $authenticationUtils;

    /** @var FormFactoryInterface */
    protected $formFactory;

    public function __construct(LoginManagerInterface $loginManager, AuthenticationUtils $authenticationUtils, FormFactoryInterface $formFactory)
    {
        $this->loginManager = $loginManager;
        $this->authenticationUtils = $authenticationUtils;
        $this->formFactory = $formFactory;
    }

    /**
     * @Route("/login", name="krg_user_login")
     */
    public function loginAction(Request $request)
    {
        $this->loginManager->disconnectIfLogged();
        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();

        $form = $this->formFactory
            ->createNamed(null, LoginType::class, null, [
                'action' => $this->generateUrl('krg_user_login_check')
            ])
            ->add('submit', SubmitType::class, ['label' => 'security.login.submit']);

        return $this->render('@KRGUser/Security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
            'form'          => $form->createView()
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
        return $this->render('@KRGUser/Security/restricted.html.twig');
    }
}
