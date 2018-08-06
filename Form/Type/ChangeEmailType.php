<?php

namespace KRG\UserBundle\Form\Type;

use KRG\UserBundle\Entity\UserInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ChangeEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', PasswordType::class, [
                'mapped'      => false,
                'constraints' => new UserPassword(),
            ])
            ->add('email_alteration', EmailType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => UserInterface::class,
            'translation_domain' => 'KRGUserBundle',
            'label_format'       => 'form.user.%name%',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'krg_user_change_email';
    }
}
