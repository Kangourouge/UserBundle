<?php

namespace KRG\UserBundle\Message;

use KRG\UserBundle\Entity\UserInterface;
use KRG\MessageBundle\Event\AbstractMailMessage;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResetPasswordMessage extends AbstractMailMessage
{
    public function getTo()
    {
        return $this->getOption('user')->getEmail();
    }

    public function getSubject()
    {
        return $this->translator->trans('reset_password.subject', ['user' => $this->getOption('user')], 'mails');
    }

    protected function getTemplate()
    {
        return '@KRGUser/message/reset_password.html.twig';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(['user']);
        $resolver->setAllowedTypes('user', UserInterface::class);
    }
}
