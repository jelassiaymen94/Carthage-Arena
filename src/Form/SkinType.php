<?php

namespace App\Form;

use App\Entity\Game;
use App\Entity\Skin;
use App\Enum\SkinRarity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SkinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du skin',
                'attr' => ['placeholder' => 'Ex: Elderflame Vandal']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 4]
            ])
            ->add('price', IntegerType::class, [
                'label' => 'Prix (en DT ou Points)',
                'attr' => ['placeholder' => 'Ex: 2175']
            ])
            ->add('rarity', EnumType::class, [
                'class' => SkinRarity::class,
                'label' => 'Rareté',
                'choice_label' => fn($choice) => $choice->name,
                'attr' => ['class' => 'form-select text-white [&>option]:text-black']
            ])
            ->add('game', EntityType::class, [
                'class' => Game::class,
                'choice_label' => 'name',
                'label' => 'Jeu associé',
                'placeholder' => 'Sélectionner un jeu',
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
            'data_class' => Skin::class,
        ]);
    }
}
