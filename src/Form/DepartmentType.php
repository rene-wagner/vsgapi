<?php

namespace App\Form;

use App\Entity\Department;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepartmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'help' => 'Nur Kleinbuchstaben, Zahlen und Bindestriche (z. B. handball).',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Beschreibung',
                'attr' => [
                    'rows' => 10,
                ],
            ])
            ->add('departmentStats', CollectionType::class, [
                'entry_type' => DepartmentStatisticType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'by_reference' => false,
                'label' => 'Statistiken',
                'entry_options' => [
                    'label' => false,
                ],
                'prototype_name' => '__stat__',
                'attr' => [
                    'class' => 'department-stats-collection',
                ],
            ])
            ->add('trainingGroups', CollectionType::class, [
                'entry_type' => DepartmentTrainingGroupType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'by_reference' => false,
                'label' => 'Trainingsgruppen',
                'entry_options' => [
                    'label' => false,
                ],
                'prototype_name' => '__group__',
                'attr' => [
                    'class' => 'department-training-groups-collection',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Department::class,
        ]);
    }
}
