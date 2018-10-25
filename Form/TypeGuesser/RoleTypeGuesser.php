<?php

namespace KRG\UserBundle\Form\TypeGuesser;

use KRG\UserBundle\Entity\UserInterface;
use KRG\UserBundle\Form\Type\RoleType;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

class RoleTypeGuesser implements FormTypeGuesserInterface
{
    public function guessType($class, $property)
    {
        if ($property === 'roles' && in_array(UserInterface::class, class_implements($class))) {
            return new TypeGuess(RoleType::class, [], Guess::VERY_HIGH_CONFIDENCE);
        }
    }

    public function guessRequired($class, $property)
    {
    }

    public function guessMaxLength($class, $property)
    {
    }

    public function guessPattern($class, $property)
    {
    }

}