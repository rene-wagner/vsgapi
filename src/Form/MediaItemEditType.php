<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\MediaFolder;
use App\Entity\MediaItem;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaItemEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Anzeigename',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Beschreibung',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'label' => 'Kategorie',
                'required' => false,
                'placeholder' => '— Keine —',
                'choice_label' => 'name',
            ])
            ->add('folder', EntityType::class, [
                'class' => MediaFolder::class,
                'label' => 'Ordner',
                'required' => false,
                'placeholder' => '— Root —',
                'choice_label' => 'name',
                'query_builder' => fn ($r) => $r->createQueryBuilder('f')->orderBy('f.name', 'ASC'),
            ])
            ->add('cropX', IntegerType::class, [
                'label' => 'X',
                'required' => false,
                'attr' => ['min' => 0],
            ])
            ->add('cropY', IntegerType::class, [
                'label' => 'Y',
                'required' => false,
                'attr' => ['min' => 0],
            ])
            ->add('cropWidth', IntegerType::class, [
                'label' => 'Breite',
                'required' => false,
                'attr' => ['min' => 1],
            ])
            ->add('cropHeight', IntegerType::class, [
                'label' => 'Höhe',
                'required' => false,
                'attr' => ['min' => 1],
            ])
            ->add('isHiddenInApi', CheckboxType::class, [
                'label' => 'In Galerie-API ausblenden',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MediaItem::class,
        ]);
    }
}
