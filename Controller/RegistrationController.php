<?php

namespace KRG\UserBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use KRG\UserBundle\Entity\UserInterface;
use KRG\UserBundle\Form\Type\RegistrationType;
use KRG\UserBundle\Manager\LoginManagerInterface;
use KRG\UserBundle\Manager\UserManagerInterface;
use KRG\UserBundle\Event\FilterUserResponseEvent;
use KRG\UserBundle\Event\FormEvent;
use KRG\UserBundle\Event\GetResponseUserEvent;
use KRG\UserBundle\KRGUserEvents;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/register")
 */
class RegistrationController extends AbstractController
{
    /** @var LoginManagerInterface */
    protected $loginManager;

    /** @var UserManagerInterface */
    protected $userManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var SessionInterface */
    protected $session;

    /** @var string */
    protected $confirmedTargetRoute;

    public function __construct(LoginManagerInterface $loginManager, UserManagerInterface $userManager, EventDispatcherInterface $eventDispatcher, TokenStorageInterface $tokenStorage, SessionInterface $session)
    {
        $this->loginManager = $loginManager;
        $this->userManager = $userManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
    }

    /**
     * @Route("", name="krg_user_registration_register")
     */
    public function registerAction(Request $request)
    {
        $this->loginManager->disconnectIfLogged();
        $user = $this->userManager->createUser();
        $user->setEnabled(true);

        $form = $this->createForm(RegistrationType::class, null, [
            'action' => $this->generateUrl('krg_user_registration_register')
        ])
            ->setData($user)
            ->add('submit', SubmitType::class, ['label' => 'form.submit_registration']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $event = new FormEvent($form, $request);
                $this->eventDispatcher->dispatch(KRGUserEvents::REGISTRATION_SUCCESS, $event);
                $this->userManager->updateUser($user, true);

                return $event->getResponse();
            } catch (UniqueConstraintViolationException $exception) {
                $form->addError(new FormError('Votre adresse mail est déjà utilisée'));
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
     *
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
     * @Route("/confirm/{token}", name="krg_user_registration_confirm")
     * Receive the confirmation token from user email provider, login the user.
     */
    public function confirmAction(Request $request, $token)
    {
        $user = $this->userManager->findUserByConfirmationToken($token);
        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        $user->setConfirmationToken(null);
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch(KRGUserEvents::REGISTRATION_CONFIRM, $event);

        $this->userManager->updateUser($user, true);

        $url = $this->generateUrl('krg_user_registration_confirmed');
        $response = new RedirectResponse($url);

        $this->eventDispatcher->dispatch(KRGUserEvents::REGISTRATION_CONFIRMED, new FilterUserResponseEvent($user, $request, $response));

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
            'targetUrl' => $this->confirmedTargetRoute ? $this->generateUrl($this->confirmedTargetRoute) : $this->getTargetUrlFromSession($request->getSession()),
        ]);
    }

    /**
     * @return string|null
     */
    protected function getTargetUrlFromSession(SessionInterface $session)
    {
        $key = sprintf('_security.%s.target_path', $this->tokenStorage->getToken()->getProviderKey());
        if ($session->has($key)) {
            return $session->get($key);
        }

        return null;
    }

    public function setConfirmedTargetRoute($confirmedTargetRoute)
    {
        $this->confirmedTargetRoute = $confirmedTargetRoute;
    }
}
