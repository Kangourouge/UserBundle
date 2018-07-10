<?php

namespace KRG\UserBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use KRG\UserBundle\Entity\UserInterface;
use KRG\UserBundle\Util\Canonicalizer;
use KRG\UserBundle\Util\PasswordUpdaterInterface;
use KRG\UserBundle\Util\TokenGenerator;

class UserManager implements UserManagerInterface
{
    /** @var PasswordUpdaterInterface */
    private $passwordUpdater;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(PasswordUpdaterInterface $passwordUpdater, EntityManagerInterface $entityManager)
    {
        $this->passwordUpdater = $passwordUpdater;
        $this->entityManager = $entityManager;
    }

    public function createUser()
    {
        $class = $this->getClass();
        $user = new $class();

        return $user;
    }

    public function updateUser(UserInterface $user, $andFlush = false)
    {
        $this->updatePassword($user);
        $this->entityManager->persist($user);

        if ($andFlush) {
            $this->entityManager->flush();
        }
    }

    public function deleteUser(UserInterface $user)
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    public function createInvitationToken(UserInterface $user)
    {
        if (strlen($user->getInvitationToken()) === 0) {
            $user->setPlainPassword(sha1(rand()));
            $user->setInvitationToken(TokenGenerator::generateToken());
        }
    }

    public function createConfirmationToken(UserInterface $user)
    {
        if (strlen($user->getConfirmationToken()) === 0) {
            $user->setEnabled(false);
            $user->setConfirmationToken(TokenGenerator::generateToken());
        }
    }

    public function processConfirmation(UserInterface $user)
    {
        if (false === $user->isEnabled() && $user->getConfirmationToken()) {
            $user->setConfirmationToken(null);
            $user->setEnabled(true);
        }
    }

    public function getClass()
    {
        return $this->entityManager->getClassMetadata(UserInterface::class)->getName();
    }

    public function getRepository()
    {
        return $this->entityManager->getRepository($this->getClass());
    }

    public function findUserBy(array $criteria)
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    public function findUsers()
    {
        return $this->getRepository()->findAll();
    }

    public function reloadUser(UserInterface $user)
    {
        $this->entityManager->refresh($user);
    }

    public function findUserByUsername($username)
    {
        return $this->findUserByEmail($username);
    }

    public function findUserByEmail($email)
    {
        return $this->findUserBy(['emailCanonical' => Canonicalizer::canonicalize($email)]);
    }

    public function findUserByConfirmationToken($token)
    {
        return $this->findUserBy(['confirmationToken' => $token]);
    }

    public function updatePassword(UserInterface $user)
    {
        $this->passwordUpdater->hashPassword($user);
    }

    /**
     * @return PasswordUpdaterInterface
     */
    protected function getPasswordUpdater()
    {
        return $this->passwordUpdater;
    }
}
