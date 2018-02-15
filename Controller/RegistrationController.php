<?php

namespace KRG\UserBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use KRG\UserBundle\Entity\UserInterface;
use KRG\UserBundle\Form\Type\RegistrationType;
use KRG\UserBundle\Manager\LoginManagerInterface;
use KRG\UserBundle\Manager\UserManagerInterface;
use KRG\UserBundle\Event\FilterUserResponseEvent;
use KRG\UserBundle\Event\FormEvent;
use KRG\UserBundle\Event\GetResponseUserEvent;
use KRG\UserBundle\KRGUserEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/register")
 */
class RegistrationController extends AbstractController
{
    /**
     * @var string
     */
    private $confirmedTargetRoute;

    /**
     * @Route("", name="krg_user_registration_register")
     */
    public function registerAction(Request $request)
    {
        $this->container->get(LoginManagerInterface::class)->disconnectIfLogged();

        /* @var $userManager UserManagerInterface */
        $userManager = $this->container->get(UserManagerInterface::class);

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $form = $this->createForm(RegistrationType::class, null, [
            'action' => $this->generateUrl('krg_user_registration_register')
        ])
            ->setData($user)
            ->add('submit', SubmitType::class, ['label' => 'form.submit_registration']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /* @var $dispatcher EventDispatcherInterface */
                $dispatcher = $this->container->get(EventDispatcherInterface::class);
                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(KRGUserEvents::REGISTRATION_SUCCESS, $event);
                $userManager->updateUser($user, true);

                return $event->getResponse();
            } catch (UniqueConstraintViolationException $exception) {
                $form->addError(new FormError('Votre adresse mail est déjà utilisée'));
            } catch (\Exception $exception) {
                $form->addError(new FormError('Error'));
            }
        }

        return $this->render('KRGUserBundle:Registration:register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Tell the user to check their email provider.
     *
     * @Route("/check_email", name="krg_user_registration_check_email")
     */
    public function checkEmailAction(Request $request)
    {
        $session = $this->container->get(SessionInterface::class);
        $email = $session->get('krg_user_send_confirmation_email/email');

        if (empty($email)) {
            return new RedirectResponse($this->generateUrl('krg_user_registration_register'));
        }

        $session->remove('krg_user_send_confirmation_email/email');
        $user = $this->container->get(UserManagerInterface::class)->findUserByEmail($email);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }

        return $this->render('KRGUserBundle:Registration:checkEmail.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * Receive the confirmation token from user email provider, login the user.
     */
    public function confirmAction(Request $request, $token)
    {
        /* @var $userManager UserManagerInterface */
        $userManager = $this->container->get(UserManagerInterface::class);

        $user = $userManager->findUserByConfirmationToken($token);
        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        /* @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->container->get(EventDispatcherInterface::class);

        $user->setConfirmationToken(null);
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(KRGUserEvents::REGISTRATION_CONFIRM, $event);

        $userManager->updateUser($user, true);

        $url = $this->generateUrl('krg_user_registration_confirmed');
        $response = new RedirectResponse($url);

        $dispatcher->dispatch(KRGUserEvents::REGISTRATION_CONFIRMED, new FilterUserResponseEvent($user, $request, $response));

        return $response;
    }

    /**
     * @Route("/confirmed", name="krg_user_registration_confirmed")
     */
    public function confirmedAction(Request $request)
    {
        /* @var $user UserInterface */
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $targetUrl = null;
        if ($this->confirmedTargetRoute) {
            $targetUrl = $this->generateUrl($this->confirmedTargetRoute);
        }

        return $this->render('KRGUserBundle:Registration:confirmed.html.twig', [
            'user'      => $user,
            'targetUrl' => $targetUrl,
        ]);
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(), [
            '?' . LoginManagerInterface::class,
            '?' . UserManagerInterface::class,
            '?' . EventDispatcherInterface::class,
            '?' . SessionInterface::class
        ]);
    }

    public function setConfirmedTargetRoute($confirmedTargetRoute)
    {
        $this->confirmedTargetRoute = $confirmedTargetRoute;
    }
}
