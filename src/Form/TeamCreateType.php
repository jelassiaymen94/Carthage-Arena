<?php

namespace App\Form;

use App\Entity\Team;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'équipe',
                'attr' => [
                    'placeholder' => 'Ex: Carthage Eagles',
                    'class' => 'w-full bg-[#161616] border border-white/10 rounded-xl py-3.5 px-4 text-sm text-white placeholder-gray-500 focus:border-primary focus:outline-none input-glow transition-all',
                ],
            ])
            ->add('tag', TextType::class, [
                'label' => 'Tag (Abréviation)',
                'attr' => [
                    'placeholder' => 'Ex: EGLS',
                    'maxlength' => 5,
                    'class' => 'w-full bg-[#161616] border border-white/10 rounded-xl py-3.5 px-4 text-sm text-white placeholder-gray-500 focus:border-primary focus:outline-none input-glow transition-all uppercase',
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Décrivez votre équipe, vos objectifs...',
                    'rows' => 4,
                    'class' => 'w-full bg-[#161616] border border-white/10 rounded-xl py-3.5 px-4 text-sm text-white placeholder-gray-500 focus:border-primary focus:outline-none input-glow transition-all resize-none',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
        ]);
    }
}
