<?php

namespace App\Form;

use App\Entity\DepartmentTrainingSession;
use App\Entity\Location;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepartmentTrainingSessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('day', TextType::class, [
                'label' => 'Tag',
                'help' => 'z. B. Montag oder Mo',
            ])
            ->add('time', TextType::class, [
                'label' => 'Zeit',
                'help' => 'z. B. 17:00 – 22:00 Uhr',
            ])
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'choice_label' => 'name',
                'label' => 'Sportstätte',
                'required' => false,
                'placeholder' => '— Bitte wählen —',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DepartmentTrainingSession::class,
        ]);
    }
}
