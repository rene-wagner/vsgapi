<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'E-Mail',
                'attr' => ['autocomplete' => 'email'],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Vorname',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nachname',
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => !$options['is_edit'],
                'first_options' => [
                    'label' => 'Passwort',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'second_options' => [
                    'label' => 'Passwort wiederholen',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'invalid_message' => 'Die Passwörter müssen übereinstimmen.',
                'constraints' => $this->getPasswordConstraints($options['is_edit']),
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rollen',
                'choices' => [
                    'Benutzer' => 'ROLE_USER',
                    'Administrator' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
        ]);

        $resolver->setAllowedTypes('is_edit', 'bool');
    }

    private function getPasswordConstraints(bool $isEdit): array
    {
        if ($isEdit) {
            return [
                new Length(min: 8, minMessage: 'Das Passwort muss mindestens {{ limit }} Zeichen lang sein.'),
            ];
        }

        return [
            new NotBlank(message: 'Bitte geben Sie ein Passwort ein.'),
            new Length(
                min: 8,
                minMessage: 'Das Passwort muss mindestens {{ limit }} Zeichen lang sein.',
            ),
        ];
    }
}
