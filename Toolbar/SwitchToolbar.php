<?php

namespace KRG\UserBundle\Toolbar;

use KRG\EasyAdminExtensionBundle\Toolbar\ToolbarInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Symfony\Component\Templating\EngineInterface;

class SwitchToolbar implements ToolbarInterface
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var EngineInterface */
    protected $templating;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, TokenStorageInterface $tokenStorage, EngineInterface $templating)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->templating = $templating;
    }

    public function render()
    {
        if ($this->authorizationChecker->isGranted('ROLE_PREVIOUS_ADMIN')) {
            $impersonatorUser = null;
            foreach ($this->tokenStorage->getToken()->getRoles() as $role) {
                if ($role instanceof SwitchUserRole) {
                    $impersonatorUser = $role->getSource()->getUser();
                    break;
                }
            }

            return $this->templating->render('@KRGUser/toolbar/switch.html.twig', [
                'impersonatorUser' => $impersonatorUser
            ]);
        }

        return null;
    }
}
