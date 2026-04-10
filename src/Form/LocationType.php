<?php

namespace App\Form;

use App\Entity\Location;
use Symfony\Component\Form\AbstractType;
use App\Form\MediaItemSelectorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
            ])
            ->add('street', TextType::class, [
                'label' => 'Straße',
            ])
            ->add('city', TextType::class, [
                'label' => 'Stadt',
            ])
            ->add('mapsUrl', UrlType::class, [
                'label' => 'Karten-Link',
                'required' => false,
                'empty_data' => null,
                'help' => 'Optional: Link zu Google Maps o. Ä.',
            ])
            ->add('picture', MediaItemSelectorType::class, [
                'required' => false,
                'label' => 'Bild',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
        ]);
    }
}
