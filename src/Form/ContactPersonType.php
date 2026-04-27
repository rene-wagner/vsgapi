<?php

namespace App\Form;

use App\Entity\ContactPerson;
use App\Form\MediaItemSelectorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'help' => 'Nur Kleinbuchstaben, Zahlen und Bindestriche (z. B. max-mustermann).',
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Vorname',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nachname',
            ])
            ->add('position', TextType::class, [
                'label' => 'Funktion',
                'required' => false,
                'empty_data' => null,
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-Mail',
                'required' => false,
                'empty_data' => null,
            ])
            ->add('phone', TextType::class, [
                'label' => 'Telefon',
                'required' => false,
                'empty_data' => null,
            ])
            ->add('address', TextareaType::class, [
                'label' => 'Adresse',
                'required' => false,
                'empty_data' => null,
                'attr' => [
                    'rows' => 4,
                ],
            ])
            ->add('isBoard', CheckboxType::class, [
                'label' => 'Im Vorstand',
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
            'data_class' => ContactPerson::class,
        ]);
    }
}
