<?php

namespace App\Controller;

use App\Entity\Profile;
use App\Entity\User;
use App\Form\ProfileUpdateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class SettingsController extends AbstractController
{
    #[Route('/parametres', name: 'app_settings', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $profile = $user->getProfile();

        $form = $this->createForm(ProfileUpdateType::class, $user);

        // Pre-fill bio from profile
        if ($profile) {
            $form->get('bio')->setData($profile->getBio());
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle Bio
            $bio = $form->get('bio')->getData();
            if (!$profile) {
                $profile = new Profile();
                $profile->setUser($user);
                $user->setProfile($profile);
                $entityManager->persist($profile);
            }
            $profile->setBio($bio);
            $profile->setUpdatedAt(new \DateTime());

            // Handle Avatar
            $avatarFile = $form->get('avatar')->getData();
            if ($avatarFile) {
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $avatarFile->guessExtension();

                try {
                    $avatarFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/avatars',
                        $newFilename,
                    );

                    $oldAvatar = $profile->getAvatarUrl();
                    if ($oldAvatar) {
                        $oldPath = $this->getParameter('kernel.project_dir') . '/public/uploads/avatars/' . $oldAvatar;
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }

                    $profile->setAvatarUrl($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                    return $this->redirectToRoute('app_settings');
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès.');
            return $this->redirectToRoute('app_settings');
        }

        return $this->render('settings/index.html.twig', [
            'profileForm' => $form->createView(),
            'user' => [
                'name' => $user->getUsername(),
                'email' => $user->getEmail(),
                'avatar' => $profile && $profile->getAvatarUrl() ? '/uploads/avatars/' . $profile->getAvatarUrl() : 'https://i.pravatar.cc/300?img=12',
                'country' => 'Tunisie',
                'language' => 'Français',
                'timezone' => 'Africa/Tunis',
                'bio' => $profile ? $profile->getBio() : '',
            ],
        ]);
    }
}
