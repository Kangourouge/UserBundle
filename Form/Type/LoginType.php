<?php

namespace KRG\UserBundle\Form\Type;

use KRG\UserBundle\Entity\UserInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('_username', EmailType::class, [
                'property_path' => 'email',
                'label'         => 'form.user.email'
            ])
            ->add('_password', PasswordType::class, [
                'property_path' => 'password',
                'label'         => 'form.user.password'
            ])
            ->add('_remember_me', CheckboxType::class, [
                'mapped'   => false,
                'required' => false,
                'label'    => 'security.login.remember_me'
            ])
            ->add('_target_path', HiddenType::class, [
                'mapped' => false,
                'data'   => $options['target_path']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'         => UserInterface::class,
                'translation_domain' => 'KRGUserBundle',
                'csrf_field_name'    => '_csrf_token',
                'csrf_token_id'      => 'authenticate',
                'target_path'        => '/'
            ])
            ->addAllowedTypes('target_path', 'string');
    }
}
