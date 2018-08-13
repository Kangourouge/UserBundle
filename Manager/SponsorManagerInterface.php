<?php

namespace KRG\UserBundle\Manager;

use KRG\UserBundle\Entity\SponsorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface SponsorManagerInterface
{
    public function getClass();

    public function getRepository();

    public function createSponsorWithEmail(UserInterface $godfather, string $email = null);

    public function getInvitationUrl(UserInterface $godfather);

    public function sendInvitation(UserInterface $godfather, SponsorInterface $sponsor);

    public function addSponsorCode(UserInterface $user);

    public function createGodfatherRelation(UserInterface $user, string $sponsorCode);

    static public function generateSponsorCode(int $length = 8);
}
