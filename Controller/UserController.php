<?php

namespace KRG\UserBundle\Controller;

use KRG\UserBundle\Entity\UserInterface;
use KRG\UserBundle\Form\Type\ProfileType;
use KRG\UserBundle\Form\Type\ChangeEmailType;
use KRG\UserBundle\Manager\LoginManagerInterface;
use KRG\UserBundle\Message\ChangeEmailMessage;
use KRG\UserBundle\Form\Type\ChangePasswordType;
use KRG\UserBundle\Manager\UserManagerInterface;
use KRG\UserBundle\Message\ChangeEmailCancelMessage;
use KRG\MessageBundle\Service\Factory\MessageFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;

class UserController extends AbstractController
{
    /** @var UserManagerInterface */
    protected $userManager;

    /** @var LoginManagerInterface */
    protected $loginManager;

    /** @var MessageFactory */
    protected $messageFactory;

    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(
        UserManagerInterface $userManager,
        MessageFactory $messageFactory,
        LoginManagerInterface $loginManager,
        TranslatorInterface $translator)
    {
        $this->userManager = $userManager;
        $this->messageFactory = $messageFactory;
        $this->loginManager = $loginManager;
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
            ->createForm(ProfileType::class)
            ->setData($user)
            ->add('submit', SubmitType::class, ['label' => 'form.submit_profile']);

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
            ->createForm(ChangePasswordType::class)
            ->setData($user)
            ->add('submit', SubmitType::class, ['label' => 'form.user.submit_change_password']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $this->userManager->updateUser($user, true);
            $this->addFlash('notice', $this->translator->trans('change_password.flash.success', [], 'KRGUserBundle'));

            return $this->redirectToRoute('krg_user_show');
        }

        return $this->render('@KRGUser/user/changePassword.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }


    /**
     * @Route("/profile/change-email", name="krg_user_change_email")
     */
    public function changeEmailAction(Request $request)
    {
        /* @var $user UserInterface */
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form = $this
            ->createForm(ChangeEmailType::class)
            ->setData($user)
            ->add('submit', SubmitType::class, ['label' => 'form.user.submit_change_email']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $this->userManager->createCancelAlterationToken($user);
            $this->userManager->createConfirmationToken($user);

            $this->messageFactory->create(ChangeEmailCancelMessage::class, ['user' => $user])->send();
            $this->messageFactory->create(ChangeEmailMessage::class, ['user' => $user])->send();

            $this->userManager->updateUser($user, true);

            $this->addFlash('notice', $this->translator->trans('email_alteration.flash.sent', [], 'KRGUserBundle'));

            return $this->redirectToRoute('krg_user_login');
        }

        return $this->render('@KRGUser/user/changeEmail.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/confirm/alteration/{token}", name="krg_user_email_alteration_confirm")
     */
    public function confirmEmailAlterationAction(Request $request, $token)
    {
        $user = $this->userManager->findUserByConfirmationToken($token);
        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        $emailBackup = $user->getEmail();
        $user
            ->setConfirmationToken(null)
            ->setEmailBackup($emailBackup)
            ->setEmail($user->getEmailAlteration())
            ->setEnabled(true);

        $this->userManager->updateUser($user, true);

        try {
            $response = new RedirectResponse($this->generateUrl('krg_user_show'));
            $this->loginManager->logInUser($user, $response);
        } catch (AccountStatusException $ex) {
            $response = $this->redirect('/');
        }

        return $response;
    }

    /**
     * @Route("/cancel/alteration/{token}", name="krg_user_email_alteration_cancel")
     */
    public function confirmEmailCancelAlterationAction(Request $request, $token)
    {
        $user = $this->userManager->findUserByCancelAlterationToken($token);
        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        // Email address is already updated, restore email from backup
        if (null === $user->getConfirmationToken()) {
            $emailBackup = $user->getEmailBackup();
            $user
                ->setEmail($emailBackup)
                ->setEmailBackup(null);
        }

        // Cancel email update
        $user
            ->setConfirmationToken(null)
            ->setEmailAlteration(null)
            ->setEnabled(true)
            ->setCancelAlterationToken(null);

        $this->userManager->updateUser($user, true);

        try {
            $response = new RedirectResponse($this->generateUrl('krg_user_show'));
            $this->loginManager->logInUser($user, $response);
        } catch (AccountStatusException $ex) {
            $response = $this->redirect('/');
        }

        return $response;
    }

    /**
     * @Route("profile/delete", name="krg_user_delete")
     */
    public function deleteAction()
    {
        $user = $this->getUser();

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('krg_user_login');
    }
}
