<?php

namespace KRG\UserBundle\Controller;

use KRG\MessageBundle\Event\MessageEvents;
use KRG\UserBundle\Manager\LoginManagerInterface;
use KRG\UserBundle\Manager\UserManagerInterface;
use KRG\UserBundle\Event\FormEvent;
use KRG\UserBundle\Event\GetResponseUserEvent;
use KRG\UserBundle\Form\Type\ResetType;
use KRG\UserBundle\KRGUserEvents;
use KRG\UserBundle\Message\ResetPasswordMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/reset")
 */
class ResetController extends AbstractController
{
    /**
     * @Route("", name="krg_user_reset_request")
     */
    public function requestAction(Request $request)
    {
        $this->container->get(LoginManagerInterface::class)->disconnectIfLogged();

        return $this->render('KRGUserBundle:Reset:request.html.twig');
    }

    /**
     * @Route("/send", name="krg_user_reset_request_send")
     */
    public function sendEmailAction(Request $request)
    {
        $this->container->get(LoginManagerInterface::class)->disconnectIfLogged();

        /* @var $userManager UserManagerInterface */
        $userManager = $this->container->get(UserManagerInterface::class);
        $username = $request->request->get('username');
        $user = $userManager->findUserByEmail($username);
        if ($user) {
            /** @var $dispatcher EventDispatcherInterface */
            $dispatcher = $this->container->get(EventDispatcherInterface::class);

            $userManager->createConfirmationToken($user);
            $userManager->updateUser($user, true);
            $dispatcher->dispatch(MessageEvents::SEND, new ResetPasswordMessage($user));

            $event = new GetResponseUserEvent($user, $request);
            $dispatcher->dispatch(KRGUserEvents::RESETTING_RESET_REQUEST, $event);

            return $this->redirectToRoute('krg_user_reset_request_send');
        }

        return $this->render('KRGUserBundle:Reset:sendEmail.html.twig');
    }

    /**
     * @Route("/check/{token}", name="krg_user_reset")
     */
    public function resetAction(Request $request, $token)
    {
        $this->container->get(LoginManagerInterface::class)->disconnectIfLogged();

        /* @var $userManager UserManagerInterface */
        $userManager = $this->container->get(UserManagerInterface::class);

        $user = $userManager->findUserByConfirmationToken($token);
        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with "confirmation token" does not exist for value "%s"', $token));
        }

        $form = $this->createForm(ResetType::class)
            ->setData($user)
            ->add('submit', SubmitType::class, ['label' => 'form.submit_reset_password']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $userManager->processConfirmation($user);
            $userManager->updateUser($user, true);

            /** @var $dispatcher EventDispatcherInterface */
            $dispatcher = $this->container->get(EventDispatcherInterface::class);
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(KRGUserEvents::RESETTING_RESET_COMPLETED, $event);

            $flashMessage = $this->container->get(TranslatorInterface::class)->trans('change_password.flash.success', [], 'KRGUserBundle');
            $this->addFlash('notice', $flashMessage);

            return $this->redirectToRoute('krg_user_login');
        }

        return $this->render('KRGUserBundle:Reset:reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(), [
            '?'.UserManagerInterface::class,
            '?'.LoginManagerInterface::class,
            '?'.EventDispatcherInterface::class,
            '?'.TranslatorInterface::class
        ]);
    }
}
