<?php

namespace KRG\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SponsoringType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('emails', CollectionType::class, [
                'entry_type' => EmailType::class,
                'entry_options' => [
                    'label' => 'sponsor.email',
                ],
                'data'       => array_fill(0, $options['nb_emails'], ''),
                'required'   => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'KRGUserBundle',
            'nb_emails'          => 5,
        ]);

        $resolver->addAllowedTypes('nb_emails', ['integer']);
    }
}
