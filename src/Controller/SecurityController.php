<?php

namespace App\Controller;

use App\Entity\Profile;
use App\Entity\User;
use App\Enum\AccountStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/inscription', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        if ($request->isMethod('POST')) {
            $username = trim($request->request->get('username'));
            $email = trim($request->request->get('email'));
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');

            // Basic validation
            if (empty($username) || empty($email) || empty($password)) {
                $this->addFlash('error', 'Tous les champs sont obligatoires.');
                return $this->redirectToRoute('app_register');
            }

            if ($password !== $confirmPassword) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                return $this->redirectToRoute('app_register');
            }

            // Check if user exists
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existingUser) {
                $this->addFlash('error', 'Cet email est déjà utilisé.');
                return $this->redirectToRoute('app_register');
            }

            $existingUsername = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
            if ($existingUsername) {
                $this->addFlash('error', 'Ce nom d\'utilisateur est déjà pris.');
                return $this->redirectToRoute('app_register');
            }

            $user = new User();
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setStatus(AccountStatus::ACTIVE);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $password
                )
            );

            // Create empty profile
            $profile = new Profile();
            $profile->setUser($user);
            $user->setProfile($profile);

            $entityManager->persist($user);
            $entityManager->persist($profile);
            $entityManager->flush();

            // Auto login would be nice here, but for simplicity let's redirect to login
            $this->addFlash('success', 'Compte créé avec succès ! Connectez-vous.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig');
    }

    #[Route('/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
