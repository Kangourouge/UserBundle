<?php

namespace KRG\UserBundle\Event\Subscriber;

use KRG\MessageBundle\Event\MessageEvents;
use KRG\UserBundle\Entity\UserInterface;
use KRG\UserBundle\Manager\UserManagerInterface;
use KRG\UserBundle\Event\FormEvent;
use KRG\UserBundle\KRGUserEvents;
use KRG\UserBundle\Message\RegistrationCheckEmailMessage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailConfirmationListener implements EventSubscriberInterface
{
    private $router;
    private $dispatcher;
    private $userManager;
    private $session;

    /**
     * EmailConfirmationListener constructor.
     * @param UrlGeneratorInterface $router
     * @param EventDispatcherInterface $dispatcher
     * @param UserManagerInterface $userManager
     * @param SessionInterface $session
     */
    public function __construct(UrlGeneratorInterface $router, EventDispatcherInterface $dispatcher, UserManagerInterface $userManager, SessionInterface $session)
    {
        $this->router = $router;
        $this->dispatcher = $dispatcher;
        $this->userManager = $userManager;
        $this->session = $session;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KRGUserEvents::REGISTRATION_SUCCESS => 'onRegistrationSuccess',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function onRegistrationSuccess(FormEvent $event)
    {
        /* @var $user UserInterface */
        $user = $event->getForm()->getData();

        $this->userManager->createConfirmationToken($user);
        $this->userManager->updateUser($user, true);

        $message = new RegistrationCheckEmailMessage($user);
        $this->dispatcher->dispatch(MessageEvents::SEND, $message);

        $this->session->set('krg_user_send_confirmation_email/email', $user->getEmail());

        // Post registration redirection
        $event->setResponse(new RedirectResponse($this->router->generate('krg_user_registration_check_email')));
    }
}
