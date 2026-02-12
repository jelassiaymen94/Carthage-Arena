<?php

namespace App\Form;

use App\Entity\Game;
use App\Entity\Team;
use App\Entity\Tournoi;
use App\Entity\User;
use App\Enum\TournamentStatus;
use App\Enum\TournamentType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TournoiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('game', EntityType::class, [
                'class' => Game::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Sélectionner un jeu',
                'attr' => ['class' => 'form-select']
            ])
            ->add('dateDebut', \Symfony\Component\Form\Extension\Core\Type\DateType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('dateFin', \Symfony\Component\Form\Extension\Core\Type\DateType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('nbEquipesMax', \Symfony\Component\Form\Extension\Core\Type\IntegerType::class)
            ->add('prizePool', \Symfony\Component\Form\Extension\Core\Type\IntegerType::class)
            ->add('status', EnumType::class, [
                'class' => TournamentStatus::class,
                'choice_label' => fn(TournamentStatus $choice) => $choice->label(),
                'attr' => ['class' => 'form-select text-white [&>option]:text-black']
            ])
            ->add('type', EnumType::class, [
                'class' => TournamentType::class,
                'choice_label' => fn(TournamentType $choice) => $choice->label(),
                'attr' => ['class' => 'form-select text-white [&>option]:text-black']
            ])
            ->add('teams', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'name',
                'multiple' => true,
                'required' => false,
                'label' => 'Registered Teams',
                'attr' => ['class' => 'form-select']
            ])
            ->add('winner', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Non connu pour le moment',
                'attr' => ['class' => 'form-select text-white [&>option]:text-black']
            ])
            ->add('referee', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
                'required' => false,
                'placeholder' => 'Sélectionner un arbitre',
                'attr' => ['class' => 'form-select text-white [&>option]:text-black']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tournoi::class,
        ]);
    }
}
