<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class UsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer une adresse email']),
                ],
            ])
            ->add('firstname', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre prénom']),
                ],
            ])
            ->add('lastname', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre nom']),
                ],
            ])
            ->add('password', PasswordType::class, [
                'mapped' => false, // Ne pas mapper ce champ à l'entité User
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un mot de passe']),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit faire au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Vous devez accepter les conditions d\'utilisation']),
                ],
                'label' => "J'accepte les conditions d'utilisation",
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}