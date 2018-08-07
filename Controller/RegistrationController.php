<?php

namespace KRG\UserBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use KRG\MessageBundle\Service\Factory\MessageFactory;
use KRG\UserBundle\Entity\UserInterface;
use KRG\UserBundle\Form\Type\RegistrationType;
use KRG\UserBundle\Manager\LoginManagerInterface;
use KRG\UserBundle\Manager\UserManagerInterface;
use KRG\UserBundle\Message\RegistrationCheckEmailMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/register")
 */
class RegistrationController extends AbstractController
{
    use TargetPathTrait;

    /** @var LoginManagerInterface */
    protected $loginManager;

    /** @var UserManagerInterface */
    protected $userManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var MessageFactory */
    protected $messageFactory;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var SessionInterface */
    protected $session;

    /** @var string */
    protected $confirmedTargetRoute;

    public function __construct(
        LoginManagerInterface $loginManager,
        UserManagerInterface $userManager,
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session,
        MessageFactory $messageFactory,
        TranslatorInterface $translator)
    {
        $this->loginManager = $loginManager;
        $this->userManager = $userManager;
        $this->messageFactory = $messageFactory;
        $this->tokenStorage = $tokenStorage;
        $this->dispatcher = $eventDispatcher;
        $this->session = $session;
        $this->translator = $translator;
    }

    /**
     * @Route("", name="krg_user_registration_register")
     */
    public function registerAction(Request $request)
    {
        $this->loginManager->disconnectIfLogged();
        $user = $this->userManager->createUser();
        $user->setEnabled(true);

        $form = $this
            ->createForm(RegistrationType::class, null, [
                'action' => $this->generateUrl('krg_user_registration_register')
            ])
            ->setData($user)
            ->add('submit', SubmitType::class, ['label' => 'form.submit_registration']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $user = $form->getData();

                $this->userManager->createConfirmationToken($user);
                $this->userManager->updateUser($user, true);

                $this->messageFactory->create(RegistrationCheckEmailMessage::class, ['user' => $user])->send();

                $this->session->set('krg_user_send_confirmation_email/email', $user->getEmail());

                return new RedirectResponse($this->generateUrl('krg_user_registration_check_email'));
            } catch (UniqueConstraintViolationException $exception) {
                $form->addError(new FormError($this->translator->trans('registration.email_exists', [], 'KRGUserBundle')));
            } catch (\Exception $exception) {
                $form->addError(new FormError('Error'));
            }
        }

        return $this->render('@KRGUser/registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Tell the user to check their email provider.
     * @Route("/check_email", name="krg_user_registration_check_email")
     */
    public function checkEmailAction(Request $request)
    {
        $email = $this->session->get('krg_user_send_confirmation_email/email');
        if (empty($email)) {
            return new RedirectResponse($this->generateUrl('krg_user_registration_register'));
        }

        $this->session->remove('krg_user_send_confirmation_email/email');
        $user = $this->userManager->findUserByEmail($email);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }

        return $this->render('@KRGUser/registration/checkEmail.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * Receive the confirmation token from user email provider, login the user.
     * @Route("/confirm/{token}", name="krg_user_registration_confirm")
     */
    public function confirmAction(Request $request, $token)
    {
        $user = $this->userManager->findUserByConfirmationToken($token);
        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        $user->setConfirmationToken(null);
        $user->setEnabled(true);
        $this->userManager->updateUser($user, true);

        try {
            $response = new RedirectResponse($this->generateUrl('krg_user_registration_confirmed'));
            $this->loginManager->logInUser($user, $response);
        } catch (AccountStatusException $ex) {
            $response = $this->redirect('/');
        }

        return $response;
    }

    /**
     * @Route("/confirmed", name="krg_user_registration_confirmed")
     */
    public function confirmedAction(Request $request)
    {
        /* @var $user UserInterface */
        $user = $this->getUser();

        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->render('@KRGUser/registration/confirmed.html.twig', [
            'user'      => $user,
            'targetUrl' => $this->confirmedTargetRoute ? $this->generateUrl($this->confirmedTargetRoute) : $this->getTargetPath($request->getSession(), $this->tokenStorage->getToken()->getProviderKey()),
        ]);
    }

    public function setConfirmedTargetRoute($confirmedTargetRoute)
    {
        $this->confirmedTargetRoute = $confirmedTargetRoute;
    }
}
