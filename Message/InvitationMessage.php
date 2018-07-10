<?php

namespace KRG\UserBundle\Message;

use KRG\UserBundle\Entity\UserInterface;
use KRG\MessageBundle\Event\AbstractMailMessage;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvitationMessage extends AbstractMailMessage
{
    public function getTo()
    {
        return $this->user->getEmail();
    }

    public function getSubject()
    {
        return $this->translator->trans('mail.subject.invitation', ['user' => $this->user], 'mails');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(['user']);
        $resolver->setAllowedTypes('user', UserInterface::class);
    }
}
