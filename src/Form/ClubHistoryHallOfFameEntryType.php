<?php

namespace App\Form;

use App\Entity\ClubHistoryHallOfFameEntry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClubHistoryHallOfFameEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('year', IntegerType::class, [
                'label' => 'Jahr',
                'attr' => [
                    'class' => 'block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500',
                ],
            ])
            ->add('title', TextType::class, [
                'label' => 'Titel',
                'attr' => [
                    'class' => 'block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Beschreibung',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'class' => 'block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClubHistoryHallOfFameEntry::class,
        ]);
    }
}
