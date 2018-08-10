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

    public function createSponsor(string $email, UserInterface $godfather)
    {
        if (null !== $this->userManager->findUserByEmail($email)) {
            return null;
        }

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
            $exists = $this->findUserBy(['sponsorCode' => $code]);

            if (null === $exists) {
                break;
            }
        }

        $user->setSponsorCode($code);
        $this->userManager->updateUser($user, true);
    }

    public function createGodfatherRelation(UserInterface $user, string $sponsorCode = null)
    {
        if ($sponsorCode || strlen($sponsorCode) === 0) {
            return null;
        }

        $sponsors = $this->entityManager->getRepository(SponsorInterface::class)
                                        ->findBySponsorCodeAndEmail($sponsorCode, $user->getEmail());

        /** @var $sponsor SponsorInterface */
        foreach ($sponsors as $sponsor) {
            $sponsor->setGodson($user);
        }

        return $user;
    }

    static public function generateSponsorCode(int $length = 8)
    {
        return substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
    }
}
