<?php

namespace AppBundle\Form\Issue;

use AppBundle\Entity\Issue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IssueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('category', TextType::class)
            ->add('description', TextareaType::class)
            ->add('priority', ChoiceType::class, [
                'choices' => [
                    'Low' => 'Low',
                    'Normal' => 'Normal',
                    'High' => 'High'
                ],
            ])
            ->add('dueDate', DateTimeType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Issue::class,
        ]);
    }
}