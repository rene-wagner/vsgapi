<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\MediaFolder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class MediaItemUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentFolder = $options['current_folder'];

        $builder
            ->add('file', FileType::class, [
                'label' => 'Datei',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(message: 'Bitte eine Datei auswählen.'),
                    new File(
                        maxSize: '10M',
                        mimeTypes: [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                            'application/pdf',
                        ],
                        mimeTypesMessage: 'Nur JPG, PNG, WebP oder PDF sind erlaubt.',
                    ),
                ],
            ])
            ->add('folder', EntityType::class, [
                'class' => MediaFolder::class,
                'label' => 'Ordner',
                'required' => false,
                'placeholder' => '— Root —',
                'choice_label' => 'name',
                'mapped' => false,
                'query_builder' => fn ($r) => $r->createQueryBuilder('f')->orderBy('f.name', 'ASC'),
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'label' => 'Kategorie',
                'required' => false,
                'placeholder' => '— Keine —',
                'choice_label' => 'name',
                'mapped' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Beschreibung',
                'required' => false,
                'mapped' => false,
                'attr' => ['rows' => 3],
            ])
            ->add('name', TextType::class, [
                'label' => 'Anzeigename (optional)',
                'required' => false,
                'mapped' => false,
                'help' => 'Leer lassen, um den Originaldateinamen zu übernehmen.',
            ]);

        if ($currentFolder instanceof MediaFolder) {
            $builder->get('folder')->setData($currentFolder);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'current_folder' => null,
        ]);
        $resolver->setAllowedTypes('current_folder', ['null', MediaFolder::class]);
    }
}
