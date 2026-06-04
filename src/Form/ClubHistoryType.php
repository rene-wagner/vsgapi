<?php

namespace App\Form;

use App\Entity\ClubHistory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClubHistoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('foundingDate', DateType::class, [
                'label' => 'Gründungsdatum',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('clubStatistics', CollectionType::class, [
                'entry_type' => ClubStatisticsType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'by_reference' => false,
                'label' => 'Vereinsstatistiken',
                'entry_options' => [
                    'label' => false,
                ],
            ])
            ->add('milestones', CollectionType::class, [
                'entry_type' => ClubHistoryMilestoneType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'by_reference' => false,
                'label' => 'Meilensteine der Chronik',
                'entry_options' => [
                    'label' => false,
                ],
            ])
            ->add('hallOfFameEntries', CollectionType::class, [
                'entry_type' => ClubHistoryHallOfFameEntryType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'by_reference' => false,
                'label' => 'Besondere Errungenschaften',
                'entry_options' => [
                    'label' => false,
                ],
            ])
            ->add('specialEvents', CollectionType::class, [
                'entry_type' => ClubHistorySpecialEventType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'by_reference' => false,
                'label' => 'Besondere Veranstaltungen',
                'entry_options' => [
                    'label' => false,
                ],
            ])
            ->add('membershipStats', CollectionType::class, [
                'entry_type' => ClubHistoryMembershipStatType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'by_reference' => false,
                'label' => 'Mitgliederanzahl pro Jahr',
                'entry_options' => [
                    'label' => false,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClubHistory::class,
        ]);
    }
}
