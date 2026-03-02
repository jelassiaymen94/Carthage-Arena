<?php

namespace App\Form;

use App\Entity\Game;
use App\Enum\GameStatus;
use App\Enum\GameType as GameTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du Jeu',
                'attr' => ['placeholder' => 'ex: Valorant']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 4]
            ])
            ->add('type', EnumType::class, [
                'class' => GameTypeEnum::class,
                'label' => 'Cat├®gorie',
                'choice_label' => fn($choice) => $choice->name
            ])
            ->add('status', EnumType::class, [
                'class' => GameStatus::class,
                'label' => 'Statut',
                'choice_label' => fn($choice) => $choice->name
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => 'Uploader une image',
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
                'image_uri' => true,
                'asset_helper' => true,
            ])
            ->add('imageUrl', UrlType::class, [
                'label' => 'OU URL de l\'image existante',
                'required' => false,
                'attr' => ['placeholder' => 'https://example.com/image.jpg']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}
