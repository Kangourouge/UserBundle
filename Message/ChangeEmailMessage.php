<?php

namespace KRG\UserBundle\Message;

use KRG\UserBundle\Entity\UserInterface;
use KRG\MessageBundle\Event\AbstractMailMessage;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangeEmailMessage extends AbstractMailMessage
{
    public function getTo()
    {
        return $this->getOption('user')->getEmailAlteration();
    }

    public function getSubject(array $parameters = [])
    {
        return $this->translator->trans('change_email.subject', ['user' => $this->getOption('user')], 'mails');
    }

    protected function getTemplate()
    {
        return '@KRGUser/message/change_email.html.twig';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(['user']);
        $resolver->setAllowedTypes('user', UserInterface::class);
    }
}
