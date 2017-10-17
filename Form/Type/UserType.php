<?php

namespace KRG\UserBundle\Form\Type;

use KRG\UserBundle\Entity\UserInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', EmailType::class)
            ->add('firstname', TextType::class)
            ->add('lastname', TextType::class)
            ->add('email', EmailType::class);

        $builder->addEventListener(FormEvents::POST_SET_DATA, [$this, 'onPostSetData']);
    }

    public function onPostSetData(FormEvent $event)
    {
        /* @var $user UserInterface */
        $user = $event->getData();
        /* @var $form FormInterface */
        $form = $event->getForm();

        if ($user === null) {
            return;
        }

        if (strlen($user->getPassword()) === 0) {
            $form->add('plainPassword', RepeatedType::class, [
                'type'            => PasswordType::class,
                'first_options'   => ['label' => 'form.password'],
                'second_options'  => ['label' => 'form.password_confirmation'],
                'invalid_message' => 'Mismatch', // TODO: translate
                'required'        => true
            ]);
        }

        if (false === $user->getTerms()) {
            $form->add('terms', CheckboxType::class, [
                'label'    => 'Terms',
                'required' => true,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => UserInterface::class,
            'translation_domain' => 'KRGUserBundle',
            'label_format'       => 'form.%name%'
        ]);
    }

    public function getBlockPrefix()
    {
        return 'krg_user_profile';
    }
}
