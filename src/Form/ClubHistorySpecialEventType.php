<?php

namespace App\Form;

use App\Entity\ClubHistorySpecialEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClubHistorySpecialEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titel',
                'attr' => [
                    'class' => 'block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500',
                ],
            ])
            ->add('date', DateType::class, [
                'label' => 'Datum',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
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
            'data_class' => ClubHistorySpecialEvent::class,
        ]);
    }
}
