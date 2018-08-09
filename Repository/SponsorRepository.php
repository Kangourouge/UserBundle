<?php

namespace KRG\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SponsorRepository extends EntityRepository
{
    public function findBySponsorCodeAndEmail(string $sponsorCode, string $email)
    {
        return $this
            ->createQueryBuilder('sponsor')
            ->join('sponsor.godfather', 'user')
            ->where('sponsor.email = :email')
            ->andWhere('user.sponsorCode = :sponsorCode')
            ->setParameters([
                'email'       => $email,
                'sponsorCode' => $sponsorCode,
            ])
            ->getQuery()
            ->getResult();
    }
}
