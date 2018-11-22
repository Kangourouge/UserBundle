<?php

namespace KRG\UserBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use KRG\MessageBundle\Service\Factory\MessageFactory;
use KRG\UserBundle\Entity\SponsorInterface;
use KRG\UserBundle\Message\SponsoringMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SponsorManager implements SponsorManagerInterface
{
    const SPONSOR_PARAM = 'sp';

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var UserManagerInterface */
    protected $userManager;

    /** @var RouterInterface */
    protected $router;

    /** @var MessageFactory */
    protected $messageFactory;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserManagerInterface $userManager,
        RouterInterface $router,
        MessageFactory $messageFactory
    ) {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->router = $router;
        $this->messageFactory = $messageFactory;
    }

    public function getClass()
    {
        return $this->entityManager->getClassMetadata(SponsorInterface::class)->getName();
    }

    public function getRepository()
    {
        return $this->entityManager->getRepository($this->getClass());
    }

    public function createSponsorWithEmail(UserInterface $godfather, string $email = null)
    {
        // Can not create a sponsor with an existing user email
        if (null !== $this->userManager->findUserByEmail($email)) {
            return null;
        }

        // Check if the godfather have not already invited this email
        if (false === $godfather->getSponsors()->filter(function(SponsorInterface $sponsor) use ($email) {
            return $sponsor->getEmail() === $email;
        })->isEmpty()) {
            return null;
        }

        $class = $this->getClass();
        /** @var $sponsor SponsorInterface */
        $sponsor = new $class();
        $sponsor
            ->setEmail($email)
            ->setGodfather($godfather);

        return $sponsor;
    }

    public function getInvitationUrl(UserInterface $godfather)
    {
        return sprintf('%s?%s=%s',
            $this->router->generate('krg_user_registration_register', [], UrlGeneratorInterface::ABSOLUTE_URL),
            self::SPONSOR_PARAM,
            $godfather->getSponsorCode()
        );
    }

    public function sendInvitation(UserInterface $godfather, SponsorInterface $sponsor)
    {
        return $this->messageFactory->create(SponsoringMessage::class, [
            'user' => $godfather,
            'to'   => $sponsor->getEmail(),
            'url'  => sprintf('%s&email=%s', $this->getInvitationUrl($godfather), $sponsor->getEmail()),
        ])->send();
    }

    public function addSponsorCode(UserInterface $user)
    {
        if (strlen($user->getSponsorCode() > 0)) {
            return null;
        }

        $code = null;
        while (1) {
            $code = self::generateSponsorCode();
            $exists = $this->userManager->findUserBy(['sponsorCode' => $code]);

            if (null === $exists) {
                break;
            }
        }

        $user->setSponsorCode($code);
        $this->userManager->updateUser($user, true);
    }

    public function createGodfatherRelation(UserInterface $user, string $sponsorCode)
    {
        if (strlen($sponsorCode) === 0) {
            return null;
        }

        $godfather = $this->entityManager->getRepository(UserInterface::class)->findOneBy(['sponsorCode' => $sponsorCode]);
        if (null === $godfather) {
            return null;
        }

        /** @var $sponsor SponsorInterface */
        foreach ($godfather->getSponsors() as $sponsor) {
            // Update Godfather Sponsors with registered godson
            if ($sponsor->getEmail() === $user->getEmail()) {
                $sponsor->setGodson($user);

                return $user;
            }
        }

        // In case the Sponsor Code is used without invitation
        $class = $this->getClass();
        $sponsor = new $class();
        $sponsor
            ->setGodfather($godfather)
            ->setGodson($user);

        $this->entityManager->persist($sponsor);

        return $user;
    }

    static public function generateSponsorCode(int $length = 8)
    {
        return substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
    }
}
