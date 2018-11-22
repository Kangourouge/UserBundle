<?php

namespace KRG\UserBundle\Controller;

use KRG\UserBundle\Entity\SponsorInterface;
use KRG\UserBundle\Manager\UserManager;
use KRG\UserBundle\Form\Type\SponsoringType;
use KRG\UserBundle\Manager\UserManagerInterface;
use KRG\UserBundle\Manager\SponsorManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/user/sponsor", name="krg_user_sponsor_")
 */
class SponsorController extends AbstractController
{
    /** @var SponsorManagerInterface */
    protected $sponsorManager;

    /** @var UserManagerInterface */
    protected $userManager;

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(
        SponsorManagerInterface $sponsorManager,
        UserManager $userManager,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ) {
        $this->sponsorManager = $sponsorManager;
        $this->userManager = $userManager;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * @Route("", name="index")
     */
    public function indexAction(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm(SponsoringType::class, null, [
                'action' => $this->generateUrl('krg_user_sponsor_index')
            ])
            ->add('submit', SubmitType::class, ['label' => 'form.submit_sponsoring']);

        $this->sponsorManager->addSponsorCode($user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            foreach ($data['emails'] as $email) {
                if (strlen($email) > 0) {
                    $sponsor = $this->sponsorManager->createSponsorWithEmail($user, $email);

                    if ($sponsor) {
                        $this->entityManager->persist($sponsor);
                        $this->sponsorManager->sendInvitation($user, $sponsor);
                    }
                }
            }

            $this->userManager->updateUser($user);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('sponsor.flash.success', [], 'KRGUserBundle'));

            return $this->redirectToRoute($request->attributes->get('_route'));
        }

        return $this->render('@KRGUser/sponsor/index.html.twig', [
            'form' => $form->createView(),
            'url'  => $this->sponsorManager->getInvitationUrl($user),
        ]);
    }
}
