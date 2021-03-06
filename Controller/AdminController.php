<?php

namespace KRG\UserBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use KRG\MessageBundle\Service\Factory\MessageFactory;
use KRG\UserBundle\Entity\UserInterface;
use KRG\UserBundle\Message\InvitationMessage;

class AdminController extends \KRG\EasyAdminExtensionBundle\Controller\AdminController
{
    /**
     * @Route("/switch", name="krg_user_admin_switch")
     */
    public function switchAction()
    {
        $easyadmin = $this->request->attributes->get('easyadmin');
        $entity = $easyadmin['item'];

        $error = null;
        if ($this->getUser() === $entity) {
            $error = 'switch.yourself';
        } elseif ($this->isGranted('ROLE_PREVIOUS_ADMIN')) {
            $error = 'switch.twice';
        } else {
            return $this->redirect(sprintf('%s?_switch_user=%s', '/', $entity->getUsername()));
        }

        $this->addFlash('danger', $this->get('translator')->trans($error, [], 'admin'));

        return $this->redirect(sprintf('%s?entity=%s&action=%s', $this->generateUrl('easyadmin'), $easyadmin['entity']['name'], 'list'));
    }

    protected function createNewEntity()
    {
        $user = parent::createNewEntity();
        $user->setPlainPassword(sha1(rand()));

        return $user;
    }

    public function inviteAction()
    {
        $id = $this->request->query->get('id');
        $easyadmin = $this->request->attributes->get('easyadmin');
        $entity = $easyadmin['item'];

        $this->invite($entity);

        return $this->redirectToReferrer();
    }

    public function inviteSelectionAction(array $entities)
    {
        foreach ($entities as $entity) {
            $this->invite($entity);
        }

        return $this->redirectToReferrer();
    }

    protected function invite(UserInterface $user)
    {
        $user->setPlainPassword(sha1(rand()));
        $user->setInvitationToken(sha1(rand()));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $this->get(MessageFactory::class)->create(InvitationMessage::class, ['user' => $user])->send();
    }
}
