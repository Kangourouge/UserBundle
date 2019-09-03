<?php

namespace KRG\UserBundle\Message;

use KRG\UserBundle\Entity\UserInterface;
use KRG\MessageBundle\Event\AbstractMailMessage;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvitationMessage extends AbstractMailMessage
{
    public function getTo()
    {
        return $this->getOption('user')->getEmail();
    }

    public function getSubject(array $parameters = [])
    {
        return $this->translator->trans('invitation.subject', ['user' => $this->getOption('user')], 'mails');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(['user']);
        $resolver->setAllowedTypes('user', UserInterface::class);
    }
}
