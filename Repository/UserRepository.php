<?php

namespace KRG\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use KRG\UserBundle\Entity\UserInterface;

class UserRepository extends EntityRepository
{
    public function findAdmins()
    {
        return $this
            ->createQueryBuilder('u')
            ->where('u.enabled = 1')
            ->where('u.roles LIKE :role_admin')
            ->orWhere('u.roles LIKE :role_super_admin')
            ->setParameters([
                'role_admin'       => '%"'.UserInterface::ROLE_ADMIN.'"%',
                'role_super_admin' => '%"'.UserInterface::ROLE_SUPER_ADMIN.'"%',
            ])
            ->getQuery()
            ->getResult();
    }
}
