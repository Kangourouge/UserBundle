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
    /** @var UserManagerInterface */
    protected $userManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(UserManagerInterface $userManager, EventDispatcherInterface $eventDispatcher, TranslatorInterface $translator)
    {
        $this->userManager = $userManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->translator = $translator;
    }

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

        return $this->render('@KRGUser/security/show.html.twig', [
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
            $user = $form->getData();
            $this->userManager->updateUser($user, true);

            $flashMessage = $this->translator->trans('profile.flash.success', [], 'KRGUserBundle');
            $this->addFlash('notice', $flashMessage);

            return $this->redirectToRoute('krg_user_show');
        }

        return $this->render('@KRGUser/security/edit.html.twig',[
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
            /* @var $user UserInterface */
            $user = $form->getData();
            $this->userManager->updateUser($user, true);
            $this->eventDispatcher->dispatch(KRGUserEvents::CHANGE_PASSWORD_COMPLETED, new FormEvent($form, $request));
            $flashMessage = $this->translator->trans('change_password.flash.success', [], 'KRGUserBundle');
            $this->addFlash('notice', $flashMessage);

            return $this->redirectToRoute('krg_user_show');
        }

        return $this->render('@KRGUser/security/changePassword.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("profile/delete", name="krg_user_delete")
     */
    public function deleteAction()
    {
        /* @var $user UserInterface */
        $user = $this->getUser();

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('krg_user_login');
    }
}
