<?php

namespace App\Form;

use App\Entity\Event;
use App\Enum\EventRecurrence;
use App\Form\MediaItemSelectorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titel',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Beschreibung',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                ],
            ])
            ->add('startsAt', DateTimeType::class, [
                'label' => 'Beginn',
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('endsAt', DateTimeType::class, [
                'label' => 'Ende',
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('location', TextType::class, [
                'label' => 'Ort',
                'required' => false,
            ])
            ->add('recurrence', ChoiceType::class, [
                'label' => 'Wiederholung',
                'required' => false,
                'choices' => [
                    'Täglich' => EventRecurrence::Daily,
                    'Wöchentlich' => EventRecurrence::Weekly,
                    'Monatlich' => EventRecurrence::Monthly,
                    'Jährlich' => EventRecurrence::Yearly,
                ],
                'placeholder' => 'Einzeltermin',
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('recurrenceUntil', DateTimeType::class, [
                'label' => 'Wiederholung bis',
                'widget' => 'single_text',
                'html5' => true,
                'required' => false,
            ])
            ->add('picture', MediaItemSelectorType::class, [
                'required' => false,
                'label' => 'Bild',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}