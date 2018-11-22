<?php

namespace KRG\UserBundle\Form\Type;

use KRG\UserBundle\Doctrine\DBAL\GenderEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenderType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setNormalizer('choices', function (OptionsResolver $resolver) {
            return GenderEnum::getChoices();
        });

        $resolver->setDefaults([
            'choice_translation_domain' => 'gender',
            'expanded' => true,
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
