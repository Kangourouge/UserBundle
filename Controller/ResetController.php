<?php

namespace KRG\UserBundle\Controller;

use KRG\MessageBundle\Service\Factory\MessageFactory;
use KRG\UserBundle\Manager\LoginManagerInterface;
use KRG\UserBundle\Manager\UserManagerInterface;
use KRG\UserBundle\Form\Type\ResetType;
use KRG\UserBundle\Message\ResetPasswordMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/reset")
 */
class ResetController extends AbstractController
{
    /** @var LoginManagerInterface */
    protected $loginManager;

    /** @var UserManagerInterface */
    protected $userManager;

    /** @var MessageFactory */
    protected $messageFactory;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * ResetController constructor.
     *
     * @param LoginManagerInterface $loginManager
     * @param UserManagerInterface $userManager
     * @param MessageFactory $messageFactory
     * @param TranslatorInterface $translator
     */
    public function __construct(LoginManagerInterface $loginManager, UserManagerInterface $userManager, MessageFactory $messageFactory, TranslatorInterface $translator)
    {
        $this->loginManager = $loginManager;
        $this->userManager = $userManager;
        $this->messageFactory = $messageFactory;
        $this->translator = $translator;
    }

    /**
     * @Route("", name="krg_user_reset_request")
     */
    public function requestAction(Request $request)
    {
        $this->loginManager->disconnectIfLogged();

        return $this->render('@KRGUser/reset/request.html.twig');
    }

    /**
     * @Route("/send", name="krg_user_reset_request_send")
     */
    public function sendEmailAction(Request $request)
    {
        $this->loginManager->disconnectIfLogged();

        $username = $request->request->get('username');
        $user = $this->userManager->findUserByEmail($username);
        if ($user) {
            $this->userManager->createConfirmationToken($user);
            $this->userManager->updateUser($user, true);

            $this->messageFactory->create(ResetPasswordMessage::class, ['user' => $user])->send();

            return $this->redirectToRoute('krg_user_reset_request_send');
        }

        return $this->render('@KRGUser/reset/sendEmail.html.twig');
    }

    /**
     * @Route("/check/{token}", name="krg_user_reset")
     */
    public function resetAction(Request $request, $token)
    {
        $this->loginManager->disconnectIfLogged();

        $user = $this->userManager->findUserByConfirmationToken($token);
        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with "confirmation token" does not exist for value "%s"', $token));
        }

        $form = $this
            ->createForm(ResetType::class)
            ->setData($user)
            ->add('submit', SubmitType::class, ['label' => 'form.submit_reset_password']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $this->userManager->processConfirmation($user);
            $this->userManager->updateUser($user, true);

            $this->addFlash('notice', $this->translator->trans('change_password.flash.success', [], 'KRGUserBundle'));

            return $this->redirectToRoute('krg_user_login');
        }

        return $this->render('@KRGUser/reset/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
