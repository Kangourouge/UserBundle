<?php

namespace KRG\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SponsorRepository extends EntityRepository
{
    public function findBySponsorCodeAndEmail(string $sponsorCode)
    {
        return $this
            ->createQueryBuilder('sponsor')
            ->join('sponsor.godfather', 'user')
            ->where('user.sponsorCode = :sponsorCode')
            ->setParameters([
                'sponsorCode' => $sponsorCode,
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }
}
