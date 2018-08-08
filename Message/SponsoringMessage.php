<?php

namespace KRG\UserBundle\Message;

use KRG\UserBundle\Entity\UserInterface;
use KRG\MessageBundle\Event\AbstractMailMessage;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SponsoringMessage extends AbstractMailMessage
{
	public function getTo()
	{
		return $this->getOption('user')->getEmail();
	}

	public function getSubject()
	{
        return $this->translator->trans('sponsoring.subject', [
            '%firstname%' => $this->getOption('user')->getFirstname(),
            '%lastname%'  => $this->getOption('user')->getLastname(),
        ], 'mails');
	}

    protected function getTemplate()
    {
        return '@KRGUser/message/sponsoring.html.twig';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(['user', 'url']);
        $resolver->setAllowedTypes('user', UserInterface::class);
        $resolver->setAllowedTypes('url', 'string');
    }
}
