<?php

namespace App\Form;

use App\Entity\Reclamation;
use App\Enum\ReclamationCategory;
use App\Enum\ReclamationPriority;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subject', TextType::class, [
                'label' => 'Sujet',
                'attr' => [
                    'placeholder' => 'Bref résumé du problème',
                    'class' => 'bg-white/5 border border-white/10 text-white rounded-xl px-4 py-3 w-full focus:outline-none focus:border-primary transition-colors'
                ],
                'label_attr' => ['class' => 'text-sm font-bold text-gray-300 mb-2 block']
            ])
            ->add('category', EnumType::class, [
                'class' => ReclamationCategory::class,
                'choice_label' => fn(ReclamationCategory $choice) => $choice->getLabel(),
                'label' => 'Catégorie',
                'attr' => [
                    'class' => 'bg-white/5 border border-white/10 text-white rounded-xl px-4 py-3 w-full focus:outline-none focus:border-primary transition-colors'
                ],
                'label_attr' => ['class' => 'text-sm font-bold text-gray-300 mb-2 block']
            ])
            ->add('priority', EnumType::class, [
                'class' => ReclamationPriority::class,
                'choice_label' => fn(ReclamationPriority $choice) => $choice->getLabel(),
                'label' => 'Priorité',
                // 'expanded' => true, // Use radio buttons if preferred, keeping select for cleaner look
                'attr' => [
                    'class' => 'bg-white/5 border border-white/10 text-white rounded-xl px-4 py-3 w-full focus:outline-none focus:border-primary transition-colors'
                ],
                'label_attr' => ['class' => 'text-sm font-bold text-gray-300 mb-2 block']
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Description détaillée',
                'attr' => [
                    'placeholder' => 'Décrivez votre problème en détail...',
                    'rows' => 6,
                    'class' => 'bg-white/5 border border-white/10 text-white rounded-xl px-4 py-3 w-full focus:outline-none focus:border-primary transition-colors'
                ],
                'label_attr' => ['class' => 'text-sm font-bold text-gray-300 mb-2 block']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}
