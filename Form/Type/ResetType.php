<?php

namespace KRG\UserBundle\Form\Type;

use KRG\UserBundle\Entity\UserInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('plainPassword', RepeatedType::class, [
            'type'            => PasswordType::class,
            'first_options'   => ['label' => 'form.user.password'],
            'second_options'  => ['label' => 'form.user.password_confirmation'],
            'invalid_message' => 'form.user.error.password',
            'required'        => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => UserInterface::class,
            'translation_domain' => 'KRGUserBundle',
            'label_format'       => 'form.user.%name%'
        ]);
    }

    public function getBlockPrefix()
    {
        return 'krg_user_reset';
    }
}
