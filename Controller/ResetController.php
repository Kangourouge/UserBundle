<?php

namespace KRG\UserBundle\Controller;

use KRG\MessageBundle\Event\MessageDecorator;
use KRG\MessageBundle\Service\Factory\MessageFactory;
use KRG\UserBundle\Form\Type\ResetRequestType;
use KRG\UserBundle\Manager\LoginManagerInterface;
use KRG\UserBundle\Manager\UserManagerInterface;
use KRG\UserBundle\Form\Type\ResetType;
use KRG\UserBundle\Message\ResetPasswordMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
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

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var MessageFactory */
    protected $messageFactory;

    /** @var FormFactoryInterface */
    protected $formFactory;

    public function __construct(
        LoginManagerInterface $loginManager,
        UserManagerInterface $userManager,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator,
        MessageFactory $messageFactory,
        FormFactoryInterface $formFactory)
    {
        $this->loginManager = $loginManager;
        $this->userManager = $userManager;
        $this->dispatcher = $eventDispatcher;
        $this->translator = $translator;
        $this->messageFactory = $messageFactory;
        $this->formFactory = $formFactory;
    }

    /**
     * @Route("", name="krg_user_reset_request")
     */
    public function requestAction(Request $request)
    {
        $this->loginManager->disconnectIfLogged();

        $form = $this->formFactory
            ->createNamed(null, ResetRequestType::class, null, [
                'action' => $this->generateUrl('krg_user_reset_request_send')
            ])
            ->add('submit', SubmitType::class, ['label' => 'resetting.request.submit']);

        return $this->render('@KRGUser/reset/request.html.twig', [
            'form' => $form->createView()
        ]);
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

            /** @var $message MessageDecorator */
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
