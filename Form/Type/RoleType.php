<?php

namespace KRG\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RoleType extends AbstractType implements ChoiceLoaderInterface
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var array */
    private $roles;

    /**
     * Roles loaded choice list.
     *
     * The choices are lazy loaded.
     *
     * @var ArrayChoiceList
     */
    private $choiceList;

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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'multiple'                  => true,
            'required'                  => true,
            'choice_loader'             => $this,
            'choice_translation_domain' => 'KRGUserBundle',
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function loadChoiceList($value = null)
    {
        if (null !== $this->choiceList) {
            return $this->choiceList;
        }

        $choices = array_filter($this->roles, function ($role) {
            return $this->authorizationChecker->isGranted($role);
        });

        return $this->choiceList = new ArrayChoiceList(array_combine($choices, $choices), $value);
    }

    /**
     * {@inheritdoc}
     */
    public function loadChoicesForValues(array $values, $value = null)
    {
        // Optimize
        $values = array_filter($values);
        if (empty($values)) {
            return array();
        }

        // If no callable is set, values are the same as choices
        if (null === $value) {
            return $values;
        }

        return $this->loadChoiceList($value)->getChoicesForValues($values);
    }

    /**
     * {@inheritdoc}
     */
    public function loadValuesForChoices(array $choices, $value = null)
    {
        // Optimize
        $choices = array_filter($choices);
        if (empty($choices)) {
            return array();
        }

        // If no callable is set, choices are the same as values
        if (null === $value) {
            return $choices;
        }

        return $this->loadChoiceList($value)->getValuesForChoices($choices);
    }
}