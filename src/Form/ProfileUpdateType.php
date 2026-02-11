<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ProfileUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'attr' => [
                    'class' => 'w-full bg-[#161616] border border-white/10 rounded-xl py-3.5 px-4 text-sm text-white placeholder-gray-500 focus:border-primary focus:outline-none input-glow transition-all',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'attr' => [
                    'class' => 'w-full bg-[#161616] border border-white/10 rounded-xl py-3.5 px-4 text-sm text-white placeholder-gray-500 focus:border-primary focus:outline-none input-glow transition-all',
                ],
            ])
            ->add('bio', TextareaType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Biographie',
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Parlez-nous de vous...',
                    'class' => 'w-full bg-[#161616] border border-white/10 rounded-xl py-3.5 px-4 text-sm text-white placeholder-gray-500 focus:border-primary focus:outline-none input-glow transition-all resize-none',
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => 500,
                        'maxMessage' => 'La biographie ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('avatar', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Avatar',
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'L\'image ne doit pas dépasser 2 Mo.',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Format non supporté. Utilisez JPG, PNG ou WebP.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
