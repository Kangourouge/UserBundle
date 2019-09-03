<?php

namespace KRG\UserBundle\Message;

use KRG\UserBundle\Entity\UserInterface;
use KRG\MessageBundle\Event\AbstractMailMessage;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationCheckEmailMessage extends AbstractMailMessage
{
    public function getTo()
    {
        return $this->getOption('user')->getEmail();
    }

    public function getSubject(array $parameters = [])
    {
        return $this->translator->trans('registration.subject', ['user' => $this->getOption('user')], 'mails');
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

