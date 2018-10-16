<?php

namespace KRG\UserBundle\Form\TypeGuesser;

use KRG\UserBundle\Entity\UserInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

class RoleTypeGuesser implements FormTypeGuesserInterface
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var array */
    private $roles;

    /**
     * RoleTypeGuesser constructor.
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param array $roles
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, array $roles)
    {
        $this->authorizationChecker = $authorizationChecker;
        $_roles = call_user_func_array('array_merge_recursive', $roles);
        sort($_roles);
        $this->roles = array_unique(array_merge(array_keys($roles), $_roles));
    }

    public function guessType($class, $property)
    {
        if ($property === 'roles' && in_array(UserInterface::class, class_implements($class))) {

            try {
                $choices = array_filter($this->roles, function($role){
                    return $this->authorizationChecker->isGranted($role);
                });
            } catch (AuthenticationCredentialsNotFoundException $exception) {
                $choices = [];
            }

            $options = [
                'multiple' => true,
                'required' => true,
                'choices' => array_combine($choices, $choices),
                'choice_translation_domain' => 'KRGUserBundle'
            ];
            return new TypeGuess(ChoiceType::class, $options, Guess::VERY_HIGH_CONFIDENCE);
        }
    }

    public function guessRequired($class, $property)
    {
        // TODO: Implement guessRequired() method.
    }

    public function guessMaxLength($class, $property)
    {
        // TODO: Implement guessMaxLength() method.
    }

    public function guessPattern($class, $property)
    {
        // TODO: Implement guessPattern() method.
    }
}