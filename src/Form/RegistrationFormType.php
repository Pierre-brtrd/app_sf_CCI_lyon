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
use Symfony\Component\Validator\Constraints\Regex;
use Vich\UploaderBundle\Form\Type\VichImageType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Votre Email:',
                'required' => true,
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Éditeur' => 'ROLE_EDITOR',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'label' => 'Roles:',
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'constraints' => [
                    new Regex(
                        '/^((?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9]).{6,})\S$/',
                        'Votre mot de passe doit comporter au moins 6 caractères, une lettre majuscule, une lettre miniscule et 1 chiffre sans espace blanc'
                    ),
                ],
                'first_options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                    'label' => 'Mot de passe',
                ],
                'second_options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                    'label' => 'Répétez le mot de passe',
                ],
                'invalid_message' => 'Les mot de passe ne correspondent pas.',
                'mapped' => false,
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom:',
                'required' => true,
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom:',
                'required' => true,
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse:',
                'required' => true,
            ])
            ->add('zipCode', TextType::class, [
                'label' => 'Code postal:',
                'required' => true,
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville:',
                'required' => true,
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => 'Image: ',
                'required' => false,
                'download_uri' => false,
                'image_uri' => true,
                'by_reference' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
