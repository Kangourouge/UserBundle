<?php

namespace KRG\UserBundle\Message;

use KRG\MessageBundle\Event\AbstractMessage;
use KRG\UserBundle\Entity\UserInterface;
use Symfony\Component\Templating\EngineInterface;

class RegistrationCheckEmailMessage extends AbstractMessage
{
    /**
     * @var UserInterface
     */
    private $user;

    /**
     * RegistrationCheckEmailMessage constructor.
     * @param UserInterface $user
     */
    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    public function getTo()
    {
        return $this->user->getEmail();
    }

    public function getSubject()
    {
        return 'Registration check email';
    }

    public function getBody(EngineInterface $templating)
    {
        return $templating->render('KRGUserBundle:Message:registration_check_email.html.twig', [
            'user' => $this->user
        ]);
    }
}
