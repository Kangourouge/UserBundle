<?php

namespace KRG\UserBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use KRG\MessageBundle\Service\Factory\MessageFactory;
use KRG\UserBundle\DependencyInjection\KRGUserExtension;
use KRG\UserBundle\Entity\UserInterface;
use KRG\UserBundle\Form\Type\SponsoringType;
use KRG\UserBundle\Message\SponsoringMessage;
use KRG\UserBundle\Util\UserManipulator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/user/sponsor", name="krg_user_sponsor_")
 */
class SponsorController extends AbstractController
{
    /** @var MessageFactory */
    protected $messageFactory;

    /** @var UserManipulator */
    protected $userManipulator;

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(
        MessageFactory $messageFactory,
        UserManipulator $userManipulator,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    )
    {
        $this->messageFactory = $messageFactory;
        $this->userManipulator = $userManipulator;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * @Route("", name="index")
     */
    public function indexAction(Request $request)
    {
        $user = $this->getUser();
        $form = $this
            ->createForm(SponsoringType::class, null,
                ['action' => $this->generateUrl('krg_user_sponsor_index')]
            )
            ->add('submit', SubmitType::class, ['label' => 'form.submit_sponsoring']);

        // Add sponsor code if it does not exists
        $this->userManipulator->addSponsorCode($user->getEmailCanonical());

        // Registration url with sponsor code
        $url = sprintf('%s?%s=%s',
            $this->generateUrl('krg_user_registration_register', [], UrlGeneratorInterface::ABSOLUTE_URL),
            KRGUserExtension::SPONSOR_PARAM,
            $user->getSponsorCode()
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $success = false;

            // Send mail to each user
            foreach ($data['emails'] as $email) {
                if (strlen($email) > 0) {
                    if (null === $this->entityManager->getRepository(UserInterface::class)->findOneBy(['email' => $email])) {
                        $this->messageFactory->create(SponsoringMessage::class, [
                            'user' => $user,
                            'url'  => sprintf('%s&email=%s', $url, $email),
                        ])->send();
                    }
                    $success = true;
                }
            }

            if ($success) {
                $this->addFlash('success', $this->translator->trans('sponsor.flash.success', [], 'KRGUserBundle'));
            }

            return $this->redirectToRoute('krg_user_show');
        }

        return $this->render('@KRGUser/sponsor/index.html.twig', [
            'form' => $form->createView(),
            'url'  => $url
        ]);
    }
}
