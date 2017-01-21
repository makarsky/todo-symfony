<?php
namespace AppBundle\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class RecoveryPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('new_password', RepeatedType::class, [
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
            ->add('send', SubmitType::class, [
                'label' => 'Submit',
                'attr' => [
                    'class' => 'btn btn-primary',
                    'style' => 'margin-bottom:15px'
                ]
            ]);
    }
}