<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\MediaFolder;
use App\Entity\MediaItem;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaItemEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var bool $showRestoreOriginal */
        $showRestoreOriginal = $options['show_restore_original'];

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
            ->add('crop_x', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('crop_y', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('crop_w', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('crop_h', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('crop_natural_w', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('crop_natural_h', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ]);

        if ($showRestoreOriginal) {
            $builder->add('restore_original', SubmitType::class, [
                'label' => 'Original wiederherstellen',
                'attr' => [
                    'class' => 'btn btn-outline-warning',
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MediaItem::class,
            'show_restore_original' => false,
        ]);
        $resolver->setAllowedTypes('show_restore_original', 'bool');
    }
}
