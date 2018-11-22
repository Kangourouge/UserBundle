<?php

namespace KRG\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ResetRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', TextType::class, [
            'label' => 'resetting.request.username'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           'mapped'             => false,
           'translation_domain' => 'KRGUserBundle',
           'csrf_protection'    => false
        ]);
    }

    public function getBlockPrefix()
    {
        return 'krg_user_reset_request';
    }
}
