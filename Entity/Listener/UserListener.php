<?php

namespace KRG\UserBundle\Entity\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use KRG\UserBundle\Entity\UserInterface;
use KRG\UserBundle\Manager\UserManagerInterface;

class UserListener implements EventSubscriber
{
    /** @var UserManagerInterface */
    private $userManager;

    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    public function getSubscribedEvents()
    {
        return [
            'prePersist',
            'preUpdate',
        ];
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        $entity = $event->getObject();
        if ($entity instanceof UserInterface) {
            $this->prePersistOrUpdate($entity);
        }
    }

    public function preUpdate(LifecycleEventArgs $event)
    {
        $entity = $event->getObject();
        if ($entity instanceof UserInterface) {
            $this->prePersistOrUpdate($entity);
        }
    }

    public function prePersistOrUpdate(UserInterface $user)
    {
        if (strlen($user->getPlainPassword()) > 0) {
            $this->userManager->updatePassword($user);
        }
    }
}
