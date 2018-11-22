<?php

namespace KRG\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ConfirmType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('confirm_delete', CheckboxType::class, [
                'required'    => true,
                'constraints' => new IsTrue(),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'KRGUserBundle',
            'label_format'       => 'form.user.%name%'
        ]);
    }

    public function getBlockPrefix()
    {
        return 'krg_user_confirm';
    }
}
