<?php

namespace App\Form;

use App\Entity\User;
use App\Enum\AccountStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $inputClass = 'w-full bg-[#161616] border border-white/10 rounded-xl py-3.5 px-4 text-sm text-white placeholder-gray-500 focus:border-primary focus:outline-none input-glow transition-all';

        $builder
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'attr' => ['class' => $inputClass],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['class' => $inputClass],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôles',
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Joueur Pro' => 'ROLE_PRO',
                    'Arbitre' => 'ROLE_REFEREE',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
                'attr' => ['class' => 'flex flex-wrap gap-4'],
            ])
            ->add('status', EnumType::class, [
                'class' => AccountStatus::class,
                'label' => 'Statut du compte',
                'attr' => ['class' => $inputClass],
            ])
            ->add('balance', IntegerType::class, [
                'label' => 'Solde (CP)',
                'attr' => ['class' => $inputClass],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    } // 
}
