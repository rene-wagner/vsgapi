<?php

namespace App\Form;

use App\Entity\MediaFolder;
use App\Service\Media\MediaFolderChoiceBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaFolderType extends AbstractType
{
    public function __construct(private readonly MediaFolderChoiceBuilder $mediaFolderChoiceBuilder)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $excludeId = $options['exclude_folder_id'];

        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
            ])
            ->add('parent', EntityType::class, [
                'class' => MediaFolder::class,
                'label' => 'Übergeordneter Ordner',
                'required' => false,
                'placeholder' => '— Hauptebene —',
                'choices' => $this->mediaFolderChoiceBuilder->buildIndentedChoices($excludeId),
                'choice_label' => fn (MediaFolder $folder) => $this->mediaFolderChoiceBuilder->buildIndentedLabel($folder),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MediaFolder::class,
            'exclude_folder_id' => null,
        ]);
        $resolver->setAllowedTypes('exclude_folder_id', ['null', 'int']);
    }
}
