<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'first_options' => [
                    'label' => 'Neues Passwort',
                    'constraints' => [
                        new NotBlank(message: 'Bitte geben Sie ein Passwort ein.'),
                        new Length(
                            min: 8,
                            minMessage: 'Das Passwort muss mindestens {{ limit }} Zeichen lang sein.',
                            max: 4096,
                        ),
                    ],
                ],
                'second_options' => [
                    'label' => 'Passwort wiederholen',
                ],
                'invalid_message' => 'Die Passwörter müssen übereinstimmen.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
