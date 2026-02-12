<?php

namespace App\Form;

use App\Entity\Team;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'équipe',
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire']),
                    new Length(['min' => 3, 'max' => 50, 'minMessage' => 'Le nom doit faire au moins 3 caractères', 'maxMessage' => 'Le nom ne peut pas dépasser 50 caractères']),
                ],
            ])
            ->add('tag', TextType::class, [
                'label' => 'Tag (Abbréviation)',
                'constraints' => [
                    new NotBlank(['message' => 'Le tag est obligatoire']),
                    new Length(['min' => 3, 'max' => 5, 'minMessage' => 'Le tag doit faire au moins 3 caractères', 'maxMessage' => 'Le tag ne peut pas dépasser 5 caractères']),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'constraints' => [
                    new Length(['max' => 500, 'maxMessage' => 'La description ne peut pas dépasser 500 caractères']),
                ],
            ])
            /*
            ->add('logo', FileType::class, [
                'label' => 'Logo (Fichier image)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG, PNG, WEBP)',
                    ])
                ],
            ])
            */
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
        ]);
    }
}
