<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\File;

class MediaItemUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('files', FileType::class, [
                'label' => 'Dateien',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'admin-mediathek-file-input',
                ],
                'constraints' => [
                    new Count(
                        min: 1,
                        max: 20,
                        minMessage: 'Bitte mindestens eine Datei auswählen.',
                        maxMessage: 'Es können höchstens {{ limit }} Dateien auf einmal hochgeladen werden.',
                    ),
                    new All([
                        'constraints' => [
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
                    ]),
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'label' => 'Kategorie',
                'required' => false,
                'placeholder' => '— Keine —',
                'choice_label' => 'name',
                'mapped' => false,
                'help' => 'Gilt für alle Dateien dieses Uploads.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
