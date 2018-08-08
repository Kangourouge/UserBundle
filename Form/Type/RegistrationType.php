<?php

namespace KRG\UserBundle\Form\Type;

use KRG\UserBundle\Entity\UserInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class)
            ->add('firstname', TextType::class)
            ->add('lastname', TextType::class)
            ->add('plainPassword', RepeatedType::class, [
                'type'            => PasswordType::class,
                'first_options'   => ['label' => 'form.user.password'],
                'second_options'  => ['label' => 'form.user.password_confirmation'],
                'invalid_message' => 'form.user.error.password',
                'required'        => true,
            ])
            ->add('godfatherCode', HiddenType::class, [
                'mapped' => false,
                'data'   => $options['godfather_code']
            ])
            ->add('terms', CheckboxType::class, [
                'constraints' => new IsTrue(),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => UserInterface::class,
            'translation_domain' => 'KRGUserBundle',
            'label_format'       => 'form.user.%name%',
            'godfather_code'     => null,
        ]);

        $resolver->addAllowedTypes('godfather_code', ['string', 'null']);
    }

    public function getBlockPrefix()
    {
        return 'krg_user_registration';
    }
}
