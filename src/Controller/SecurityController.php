<?php

namespace App\Controller;

use App\Email\PasswordResetEmail;
use App\Entity\PasswordResetToken;
use App\Entity\Profile;
use App\Entity\User;
use App\Enum\AccountStatus;
use App\Form\RegistrationType;
use App\Repository\PasswordResetTokenRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Repository\LicenseRepository;

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
        EntityManagerInterface $entityManager,
        LicenseRepository $licenseRepository
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $accountType = $form->get('accountType')->getData();

            $user->setStatus(AccountStatus::ACTIVE);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $plainPassword
                )
            );

            // Assign role and license based on account type
            if ($accountType === 'referee') {
                $user->setRoles(['ROLE_REFEREE']);
                
                // Get and assign the license
                $licenseCode = $form->get('licenseId')->getData();
                if ($licenseCode) {
                    $license = $licenseRepository->findAvailableByCode($licenseCode);
                    if ($license) {
                        $license->assignToUser($user);
                        $user->setLicense($license);
                        $entityManager->persist($license);
                    }
                }
            }

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

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/mot-de-passe-oublie', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(
        Request $request,
        UserRepository $userRepository,
        PasswordResetTokenRepository $tokenRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        if ($request->isMethod('POST')) {
            // CSRF protection
            if (!$this->isCsrfTokenValid('forgot_password', $request->request->get('_token'))) {
                $this->addFlash('error', 'Token de sécurité invalide. Veuillez réessayer.');
                return $this->redirectToRoute('app_forgot_password');
            }

            $email = trim($request->request->get('email', ''));
            $user = $userRepository->findOneBy(['email' => $email]);

            // Always show a generic success message to prevent user enumeration
            if ($user && $user->getStatus() === AccountStatus::ACTIVE) {
                // Remove any existing tokens for this user before creating a new one
                $tokenRepository->deleteTokensForUser($user);

                $resetToken = new PasswordResetToken();
                $resetToken->setUser($user);
                $resetToken->setToken(bin2hex(random_bytes(32)));

                $entityManager->persist($resetToken);
                $entityManager->flush();

                $resetUrl = $this->generateUrl(
                    'app_reset_password',
                    ['token' => $resetToken->getToken()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $mailer->send(new PasswordResetEmail($user, $resetUrl));
            }

            $this->addFlash(
                'success',
                'Si un compte correspond à cette adresse e-mail, vous recevrez un lien de réinitialisation dans quelques minutes.'
            );

            return $this->redirectToRoute('app_forgot_password');
        }

        return $this->render('security/forgot_password.html.twig');
    }

    #[Route('/reinitialiser-mot-de-passe/{token}', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(
        string $token,
        Request $request,
        PasswordResetTokenRepository $tokenRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $resetToken = $tokenRepository->findValidToken($token);

        if (!$resetToken) {
            return $this->render('security/reset_password.html.twig', [
                'tokenValid' => false,
                'token' => null,
            ]);
        }

        if ($request->isMethod('POST')) {
            // CSRF protection
            if (!$this->isCsrfTokenValid('reset_password_' . $token, $request->request->get('_token'))) {
                $this->addFlash('error', 'Token de sécurité invalide. Veuillez réessayer.');
                return $this->redirectToRoute('app_reset_password', ['token' => $token]);
            }

            $newPassword = $request->request->get('password', '');
            $confirmPassword = $request->request->get('password_confirm', '');

            if (strlen($newPassword) < 6) {
                $this->addFlash('error', 'Le mot de passe doit contenir au moins 6 caractères.');
                return $this->render('security/reset_password.html.twig', [
                    'tokenValid' => true,
                    'token' => $token,
                ]);
            }

            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                return $this->render('security/reset_password.html.twig', [
                    'tokenValid' => true,
                    'token' => $token,
                ]);
            }

            $user = $resetToken->getUser();
            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));

            // Remove all reset tokens for this user
            $tokenRepository->deleteTokensForUser($user);

            $entityManager->flush();

            $this->addFlash('success', 'Mot de passe réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'tokenValid' => true,
            'token' => $token,
        ]);
    }
}
