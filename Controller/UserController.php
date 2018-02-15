<?php

namespace KRG\UserBundle\Controller;

use KRG\UserBundle\Entity\UserInterface;
use KRG\UserBundle\Form\Type\ChangePasswordType;
use KRG\UserBundle\Form\Type\UserType;
use KRG\UserBundle\Manager\UserManager;
use KRG\UserBundle\Manager\UserManagerInterface;
use KRG\UserBundle\Event\FormEvent;
use KRG\UserBundle\KRGUserEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/profile", name="krg_user_show")
     */
    public function showAction()
    {
        /* @var $user UserInterface */
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->render('KRGUserBundle:User:show.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    /**
     * @Route("/profile/edit", name="krg_user_edit")
     */
    public function editAction(Request $request)
    {
        /* @var $user UserInterface */
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form = $this->createForm(UserType::class)
            ->setData($user)
            ->add('submit', SubmitType::class, ['label' => 'form.submit_profile']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* @var $userManager UserManagerInterface */
            $userManager = $this->container->get(UserManagerInterface::class);
            $user = $form->getData();
            $userManager->updateUser($user, true);

            $flashMessage = $this->container->get(TranslatorInterface::class)->trans('profile.flash.success', [], 'KRGUserBundle');
            $this->addFlash('notice', $flashMessage);

            return $this->redirectToRoute('krg_user_show');
        }

        return $this->render('KRGUserBundle:User:edit.html.twig',[
            'form' => $form->createView(), 'user' => $user,
        ]);
    }

    /**
     * @Route("/profile/change-password", name="krg_user_change_password")
     */
    public function changePasswordAction(Request $request)
    {
        /* @var $user UserInterface */
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form = $this->createForm(ChangePasswordType::class)
            ->setData($user)
            ->add('submit', SubmitType::class, ['label' => 'form.submit_change_password']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* @var $userManager UserManagerInterface */
            $userManager = $this->container->get(UserManagerInterface::class);
            /* @var $user UserInterface */
            $user = $form->getData();
            $userManager->updateUser($user, true);

            $dispatcher = $this->container->get(EventDispatcherInterface::class);
            $dispatcher->dispatch(KRGUserEvents::CHANGE_PASSWORD_COMPLETED, new FormEvent($form, $request));

            $flashMessage = $this->container->get(TranslatorInterface::class)->trans('change_password.flash.success', [], 'KRGUserBundle');
            $this->addFlash('notice', $flashMessage);

            return $this->redirectToRoute('krg_user_show');
        }

        return $this->render('KRGUserBundle:User:changePassword.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(), [
            '?'.UserManagerInterface::class,
            '?'.EventDispatcherInterface::class,
            '?'.TranslatorInterface::class
        ]);
    }
}
