<?php

namespace KRG\UserBundle\Manager;

use KRG\UserBundle\Entity\UserInterface;
use Symfony\Component\HttpFoundation\Response;

interface LoginManagerInterface
{
    /**
     * @param UserInterface $user
     * @param Response|null $response
     */
    public function logInUser(UserInterface $user, Response $response = null);
}
