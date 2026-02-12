<?php

namespace App\Form;

use App\Entity\Game;
use App\Entity\Merch;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MerchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
                'attr' => ['placeholder' => 'Ex: T-Shirt Carthage Arena']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 4]
            ])
            ->add('price', IntegerType::class, [
                'label' => 'Prix (DT)',
                'attr' => ['placeholder' => 'Ex: 45']
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock disponible',
                'attr' => ['placeholder' => 'Ex: 100']
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de produit',
                'choices' => [
                    'Vêtement' => 'Vêtement',
                    'Accessoire' => 'Accessoire',
                    'Figurine' => 'Figurine',
                    'Poster' => 'Poster',
                    'Autre' => 'Autre',
                ],
                'attr' => ['class' => 'form-select text-white [&>option]:text-black']
            ])
            ->add('game', EntityType::class, [
                'class' => Game::class,
                'choice_label' => 'name',
                'label' => 'Jeu associé (optionnel)',
                'required' => false,
                'placeholder' => 'Aucun (Produit général)',
                'attr' => ['class' => 'form-select text-white [&>option]:text-black']
            ])
            ->add('imageUrl', UrlType::class, [
                'label' => 'URL de l\'image',
                'required' => false,
                'attr' => ['placeholder' => 'https://example.com/image.jpg']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Merch::class,
        ]);
    }
}
