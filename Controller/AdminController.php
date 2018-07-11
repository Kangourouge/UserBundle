<?php

namespace KRG\UserBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;

class AdminController extends \EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController
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

        $this->addFlash('danger', $this->get('translator')->trans($error, [], 'messages'));

        return $this->redirect(sprintf('%s?entity=%s&action=%s', $this->generateUrl('easyadmin'), $easyadmin['entity']['name'], 'list'));
    }
}
