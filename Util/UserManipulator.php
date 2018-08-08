<?php

namespace KRG\UserBundle\Util;

use KRG\UserBundle\Manager\UserManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UserManipulator
{
    /** @var UserManagerInterface */
    private $userManager;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var RequestStack */
    private $requestStack;

    public function __construct(UserManagerInterface $userManager, EventDispatcherInterface $dispatcher, RequestStack $requestStack)
    {
        $this->userManager = $userManager;
        $this->dispatcher = $dispatcher;
        $this->requestStack = $requestStack;
    }

    /**
     * Creates a user and returns it.
     */
    public function create($password, $email, $active, $superadmin)
    {
        $user = $this->userManager->createUser();
        $user->setEmail($email);
        $user->setPlainPassword($password);
        $user->setEnabled((bool) $active);
        $user->setSuperAdmin((bool) $superadmin);
        $this->userManager->updateUser($user, true);

        return $user;
    }

    /**
     * Activates the given user.
     */
    public function activate($username)
    {
        $user = $this->findUserByUsernameOrThrowException($username);
        $user->setEnabled(true);
        $this->userManager->updateUser($user, true);
    }

    /**
     * Deactivates the given user.
     */
    public function deactivate($username)
    {
        $user = $this->findUserByUsernameOrThrowException($username);
        $user->setEnabled(false);
        $this->userManager->updateUser($user, true);
    }

    /**
     * Changes the password for the given user.
     */
    public function changePassword($username, $password)
    {
        $user = $this->findUserByUsernameOrThrowException($username);
        $user->setPlainPassword($password);
        $this->userManager->updateUser($user, true);
    }

    /**
     * Promotes the given user.
     */
    public function promote($username)
    {
        $user = $this->findUserByUsernameOrThrowException($username);
        $user->setSuperAdmin(true);
        $this->userManager->updateUser($user, true);
    }

    /**
     * Demotes the given user.
     *
     * @param string $username
     */
    public function demote($username)
    {
        $user = $this->findUserByUsernameOrThrowException($username);
        $user->setSuperAdmin(false);
        $this->userManager->updateUser($user, true);
    }

    /**
     * Adds role to the given user.
     */
    public function addRole($username, $role)
    {
        $user = $this->findUserByUsernameOrThrowException($username);
        if ($user->hasRole($role)) {
            return false;
        }
        $user->addRole($role);
        $this->userManager->updateUser($user, true);

        return true;
    }

    /**
     * Removes role from the given user.
     */
    public function removeRole($username, $role)
    {
        $user = $this->findUserByUsernameOrThrowException($username);
        if (!$user->hasRole($role)) {
            return false;
        }
        $user->removeRole($role);
        $this->userManager->updateUser($user, true);

        return true;
    }

    /**
     * Finds a user by his username and throws an exception if we can't find it.
     */
    private function findUserByUsernameOrThrowException($username)
    {
        $user = $this->userManager->findUserByUsername($username);

        if (!$user) {
            throw new \InvalidArgumentException(sprintf('User identified by "%s" username does not exist.', $username));
        }

        return $user;
    }
}
