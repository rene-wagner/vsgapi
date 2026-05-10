<?php

namespace App\Form;

use App\Entity\ContactPerson;
use App\Entity\Department;
use App\Form\MediaItemSelectorType;
use App\Repository\ContactPersonRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ->add('color', ChoiceType::class, [
                'label' => 'Farbe',
                'choices' => [
                    'Lila' => 'purple',
                    'Grün' => 'green',
                    'Rot' => 'red',
                    'Blau' => 'blue',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Beschreibung',
                'attr' => [
                    'rows' => 10,
                ],
            ])
            ->add('manager', EntityType::class, [
                'class' => ContactPerson::class,
                'choice_label' => fn (ContactPerson $contactPerson): string => $contactPerson->getLastName() . ', ' . $contactPerson->getFirstName(),
                'query_builder' => fn (ContactPersonRepository $contactPersonRepository) => $contactPersonRepository
                    ->createQueryBuilder('contactPerson')
                    ->orderBy('contactPerson.lastName', 'ASC')
                    ->addOrderBy('contactPerson.firstName', 'ASC'),
                'required' => false,
                'placeholder' => 'Bitte auswählen',
                'label' => 'Abteilungsleiter',
            ])
            ->add('icon', MediaItemSelectorType::class, [
                'required' => false,
                'label' => 'Icon',
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
            ->add('departmentResults', CollectionType::class, [
                'entry_type' => DepartmentResultType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'by_reference' => false,
                'label' => 'Ergebnisdienst',
                'entry_options' => [
                    'label' => false,
                ],
                'prototype_name' => '__result__',
                'attr' => [
                    'class' => 'department-results-collection',
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
