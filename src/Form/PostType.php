<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\User;
use App\Form\MediaItemSelectorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titel',
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'help' => 'Nur Kleinbuchstaben, Zahlen und Bindestriche (z. B. mein-beitrag).',
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Inhalt (Markdown)',
                'attr' => [
                    'rows' => 18,
                    'class' => 'markdown-input',
                ],
            ])
            ->add('published', CheckboxType::class, [
                'label' => 'Veröffentlicht',
                'required' => false,
            ])
            ->add('hits', IntegerType::class, [
                'label' => 'Aufrufe',
                'help' => 'Anzahl der Aufrufe (kann manuell angepasst werden).',
                'empty_data' => '0',
            ])
            ->add('oldPost', CheckboxType::class, [
                'label' => 'Alter Beitrag (Vorgänger-Version)',
                'required' => false,
            ])
            ->add('author', EntityType::class, [
                'class' => User::class,
                'choice_label' => fn (User $user): string => $user->getFullName() . ' (' . $user->getEmail() . ')',
                'label' => 'Autor',
            ])
            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Kategorien',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'by_reference' => false,
            ])
            ->add('picture', MediaItemSelectorType::class, [
                'required' => false,
                'label' => 'Bild',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
