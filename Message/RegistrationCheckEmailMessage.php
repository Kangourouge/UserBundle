<?php

namespace KRG\UserBundle\Message;

use KRG\UserBundle\Entity\UserInterface;
use KRG\MessageBundle\Event\AbstractMailMessage;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationCheckEmailMessage extends AbstractMailMessage
{
    public function getTo()
    {
        $user = $this->getOption('user');
        return [$user->getEmail()];
    }

    public function getSubject()
    {
        $user = $this->getOption('user');
        return parent::getSubject(['user' => $user]);
    }

    protected function getTemplate()
    {
        return '@KRGUser/message/registration_check_email.html.twig';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(['user']);
        $resolver->setAllowedTypes('user', UserInterface::class);
    }
}
