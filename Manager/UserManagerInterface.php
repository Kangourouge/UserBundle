<?php

namespace KRG\UserBundle\Manager;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserManagerInterface
{
    public function createUser();

    public function updateUser(UserInterface $user, $andFlush = false);

    public function deleteUser(UserInterface $user);

    public function createInvitationToken(UserInterface $user);

    public function createConfirmationToken(UserInterface $user);

    public function getClass();

    public function getRepository();

    public function findUserBy(array $criteria);

    public function findUsers();

    public function reloadUser(UserInterface $user);

    public function findUserByUsername($username);

    public function findUserByEmail($email);

    public function findUserByConfirmationToken($token);

    public function findUserByCancelAlterationToken($token);

    public function updatePassword(UserInterface $user);

    public function createGodfatherRelation(UserInterface $user, string $sponsorCode);
}
