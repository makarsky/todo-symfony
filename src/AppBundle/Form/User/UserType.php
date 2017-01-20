<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 15.01.17
 * Time: 22:23
 */
namespace AppBundle\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'style' => 'margin-bottom:15px'
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => ['label' => 'Password', 'attr' => [
                    'class' => 'form-control',
                    'style' => 'margin-bottom:15px'
                ],],
                'second_options' => ['label' => 'Repeat Password', 'attr' => [
                    'class' => 'form-control',
                    'style' => 'margin-bottom:15px'
                ],],
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'style' => 'margin-bottom:15px'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\User',
        ]);
    }
}