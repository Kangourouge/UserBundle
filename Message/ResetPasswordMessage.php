<?php

namespace KRG\UserBundle\Message;

use KRG\MessageBundle\Event\AbstractMessage;
use KRG\UserBundle\Entity\UserInterface;
use Symfony\Component\Templating\EngineInterface;

class ResetPasswordMessage extends AbstractMessage
{
    /**
     * @var UserInterface
     */
    private $user;

    /**
     * InvitationMessage constructor.
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
        return 'Reset password';
    }

    public function getBody(EngineInterface $templating)
    {
        return $templating->render('KRGUserBundle:Message:reset_password.html.twig', [
            'user' => $this->user
        ]);
    }
}
