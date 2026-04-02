<?php

namespace App\Form;

use App\Entity\DepartmentTrainingGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepartmentTrainingGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name der Gruppe',
                'help' => 'z. B. Jugend oder Erwachsene',
            ])
            ->add('ageRange', TextType::class, [
                'label' => 'Altersgruppe',
                'help' => 'Optional, z. B. 6 – 17 oder Erwachsene',
                'required' => false,
            ])
            ->add('departmentTrainingSessions', CollectionType::class, [
                'entry_type' => DepartmentTrainingSessionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'by_reference' => false,
                'label' => 'Trainingseinheiten',
                'entry_options' => [
                    'label' => false,
                ],
                'prototype_name' => '__session__',
                'attr' => [
                    'class' => 'department-training-sessions-collection',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DepartmentTrainingGroup::class,
        ]);
    }
}
