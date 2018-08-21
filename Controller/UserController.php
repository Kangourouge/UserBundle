<?php

namespace KRG\UserBundle\Controller;

use KRG\UserBundle\Entity\UserInterface;
use KRG\UserBundle\Form\Type\ConfirmType;
use KRG\UserBundle\Form\Type\ProfileType;
use KRG\UserBundle\Manager\UserManagerInterface;
use KRG\UserBundle\Form\Type\ChangePasswordType;
use KRG\UserBundle\Manager\LoginManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    /** @var UserManagerInterface */
    protected $userManager;

    /** @var LoginManagerInterface */
    protected $loginManager;

    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(UserManagerInterface $userManager, TranslatorInterface $translator)
    {
        $this->userManager = $userManager;
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

        return $this->render('@KRGUser/user/show.html.twig', [
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

        $form = $this
            ->createForm(ProfileType::class, $user, [
                'action' => $this->generateUrl('krg_user_edit'),
            ])
            ->add('submit', SubmitType::class, [
                'label'  => 'form.submit_profile',
            ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $this->userManager->updateUser($user, true);
            $this->addFlash('notice', $this->translator->trans('profile.flash.success', [], 'KRGUserBundle'));

            return $this->redirectToRoute('krg_user_show');
        }

        return $this->render('@KRGUser/user/edit.html.twig',[
            'form' => $form->createView(),
            'user' => $user,
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

        $form = $this
            ->createForm(ChangePasswordType::class, $user, [
                'action' => $this->generateUrl('krg_user_change_password'),
            ])
            ->add('submit', SubmitType::class, [
                'label'  => 'form.user.submit_change_password',
            ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $this->userManager->updateUser($user, true);
            $this->addFlash('success', $this->translator->trans('change_password.flash.success', [], 'KRGUserBundle'));

            return $this->redirectToRoute('krg_user_show');
        }

        return $this->render('@KRGUser/user/changePassword.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/profile/delete", name="krg_user_delete")
     */
    public function deleteAction(Request $request)
    {
        $user = $this->getUser();
        $form = $this
            ->createForm(ConfirmType::class)
            ->add('submit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();

            return $this->redirectToRoute('krg_user_login');
        }

        return $this->render('@KRGUser/user/delete.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
