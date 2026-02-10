<?php

namespace App\Controller;

use App\Entity\Profile;
use App\Entity\User;
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

        if ($request->isMethod('POST')) {
            $this->handleProfileUpdate($request, $user, $entityManager, $slugger);
            return $this->redirectToRoute('app_settings');
        }

        $profile = $user->getProfile();

        return $this->render('settings/index.html.twig', [
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

    private function handleProfileUpdate(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
    ): void {
        $submittedToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('profile_update', $submittedToken)) {
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return;
        }

        $username = trim($request->request->get('name', ''));
        $email = trim($request->request->get('email', ''));
        $bio = trim($request->request->get('bio', ''));

        if (strlen($username) < 3) {
            $this->addFlash('error', 'Le nom d\'utilisateur doit contenir au moins 3 caractères.');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', 'Adresse e-mail invalide.');
            return;
        }

        if ($username !== $user->getUsername()) {
            $existing = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
            if ($existing) {
                $this->addFlash('error', 'Ce nom d\'utilisateur est déjà utilisé.');
                return;
            }
            $user->setUsername($username);
        }

        if ($email !== $user->getEmail()) {
            $existing = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existing) {
                $this->addFlash('error', 'Cette adresse e-mail est déjà utilisée.');
                return;
            }
            $user->setEmail($email);
        }

        // Handle Bio
        $profile = $user->getProfile();
        if (!$profile) {
            $profile = new Profile();
            $profile->setUser($user);
            $user->setProfile($profile);
            $entityManager->persist($profile);
        }
        $profile->setBio($bio);
        $profile->setUpdatedAt(new \DateTime());

        // Handle Avatar
        $avatarFile = $request->files->get('avatar');
        if ($avatarFile) {
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($avatarFile->getMimeType(), $allowedMimes)) {
                $this->addFlash('error', 'Format d\'image non supporté. Utilisez JPG, PNG ou WebP.');
                return;
            }

            if ($avatarFile->getSize() > 2 * 1024 * 1024) {
                $this->addFlash('error', 'L\'image ne doit pas dépasser 2 Mo.');
                return;
            }

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
                return;
            }
        }

        $entityManager->flush();
        $this->addFlash('success', 'Profil mis à jour avec succès.');
    }
}
