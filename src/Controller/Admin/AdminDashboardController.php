<?php

namespace App\Controller\Admin;

use App\Entity\Game;
use App\Enum\AccountStatus;
use App\Entity\Merch;
use App\Entity\Skin;
use App\Entity\Tournoi;
use App\Entity\User;
use App\Form\AdminNewUserType;
use App\Form\AdminUserType;
use App\Form\GameType;
use App\Form\MerchType;
use App\Form\SkinType;
use App\Form\TournoiType;
use App\Repository\GameRepository;
use App\Repository\LicenseRepository;
use App\Repository\MerchRepository;
use App\Repository\SkinRepository;
use App\Repository\TournoiRepository;
use App\Repository\UserRepository;
use App\Repository\TeamRepository;
use App\Entity\Profile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminDashboardController extends AbstractController
{
    public function __construct(
        private readonly TournoiRepository $tournoiRepository,
        private readonly UserRepository $userRepository,
        private readonly TeamRepository $teamRepository,
        private readonly GameRepository $gameRepository,
        private readonly SkinRepository $skinRepository,
        private readonly MerchRepository $merchRepository,
        private readonly LicenseRepository $licenseRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }
    #[Route('/', name: 'admin_dashboard')]
    public function index(): Response
    {
        $tournois = $this->tournoiRepository->findBy([], ['dateDebut' => 'DESC'], 5);
        $totalUsers = $this->userRepository->count([]);
        $totalTournaments = $this->tournoiRepository->count([]);

        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => [
                'totalUsers' => $totalUsers,
                'activeUsers' => $totalUsers, // Simplification for now
                'totalTournaments' => $totalTournaments,
                'activeTournaments' => count($this->tournoiRepository->findBy(['status' => 'ongoing'])),
                'totalRevenue' => '125,450 DT',
                'monthlyRevenue' => '18,200 DT',
                'totalMatches' => 3421,
                'todayMatches' => 28,
            ],
            'recentUsers' => $this->userRepository->findBy([], ['id' => 'DESC'], 5),
            'recentTournaments' => $tournois,
            'systemHealth' => [
                'serverStatus' => 'online',
                'cpuUsage' => 45,
                'memoryUsage' => 62,
                'diskUsage' => 38,
                'uptime' => '15 jours',
            ],
        ]);
    }

    #[Route('/users', name: 'admin_users')]
    public function users(Request $request): Response
    {
        $search = $request->query->get('search');
        $status = $request->query->get('status');
        $role = $request->query->get('role');

        return $this->render('admin/users/index.html.twig', [
            'users' => $this->userRepository->searchAndFilter($search, $status, $role),
        ]);
    }

    #[Route('/users/add', name: 'admin_users_add', methods: ['GET', 'POST'])]
    public function addUser(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(AdminNewUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set default password: carthage1122
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, 'carthage1122')
            );

            // createdAt is automatically set in User constructor

            // Handle license assignment if user is a referee
            if (in_array('ROLE_REFEREE', $user->getRoles())) {
                $licenseCode = $form->get('licenseId')->getData();
                if ($licenseCode) {
                    $license = $this->licenseRepository->findAvailableByCode($licenseCode);
                    if ($license) {
                        $license->assignToUser($user);
                        $user->setLicense($license);
                        $this->entityManager->persist($license);
                    }
                }
            }

            // Create empty profile for the user
            $profile = new Profile();
            $profile->setUser($user);
            $user->setProfile($profile);

            $this->entityManager->persist($user);
            $this->entityManager->persist($profile);
            $this->entityManager->flush();

            $this->addFlash('success', 'L\'utilisateur "' . $user->getUsername() . '" a été créé avec succès.');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/users/{id}/edit', name: 'admin_users_edit', methods: ['GET', 'POST'])]
    public function editUser(Request $request, User $user): Response
    {
        $form = $this->createForm(AdminUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'L\'utilisateur "' . $user->getUsername() . '" a été mis à jour avec succès.');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/users/{id}/ban', name: 'admin_users_ban', methods: ['POST'])]
    public function banUser(Request $request, User $user): Response
    {
        if (!$this->isCsrfTokenValid('ban' . $user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée (CSRF).');
            return $this->redirectToRoute('admin_users');
        }

        // Toggle status
        if ($user->getStatus() === AccountStatus::ACTIVE) {
            $user->setStatus(AccountStatus::SUSPENDED);
            $message = 'L\'utilisateur a été suspendu.';
        } else {
            $user->setStatus(AccountStatus::ACTIVE);
            $message = 'L\'utilisateur a été réactivé.';
        }

        $this->entityManager->flush();
        $this->addFlash('success', $message);

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/users/{id}/revoke-license', name: 'admin_users_revoke_license', methods: ['POST'])]
    public function revokeLicense(Request $request, User $user): Response
    {
        if (!$this->isCsrfTokenValid('revoke' . $user->getId(), $request->getPayload()->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée (CSRF).');
            return $this->redirectToRoute('admin_users');
        }

        $license = $user->getLicense();
        if ($license) {
            $user->setLicense(null);
            $license->setAssignedTo(null);
            $license->setIsUsed(false);
            $license->setUsedAt(null);
            $this->entityManager->flush();
            $this->addFlash('success', 'La licence a été révoquée pour cet utilisateur.');
        } else {
            $this->addFlash('warning', 'Cet utilisateur n\'a pas de licence.');
        }

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/users/{id}/assign-license', name: 'admin_users_assign_license', methods: ['GET', 'POST'])]
    public function assignLicense(Request $request, User $user): Response
    {
        // Check if user is a referee
        if (!in_array('ROLE_REFEREE', $user->getRoles())) {
            $this->addFlash('error', 'Seuls les arbitres peuvent avoir une licence.');
            return $this->redirectToRoute('admin_users');
        }

        // Check if user already has a license
        if ($user->getLicense()) {
            $this->addFlash('warning', 'Cet utilisateur a déjà une licence.');
            return $this->redirectToRoute('admin_users');
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('assign' . $user->getId(), $request->request->get('_token'))) {
                $this->addFlash('error', 'Action non autorisée (CSRF).');
                return $this->redirectToRoute('admin_users');
            }

            $licenseCode = $request->request->get('license_code');
            
            if (empty($licenseCode)) {
                $this->addFlash('error', 'Le code de licence est obligatoire.');
                return $this->render('admin/users/assign_license.html.twig', [
                    'user' => $user,
                ]);
            }

            $license = $this->licenseRepository->findAvailableByCode($licenseCode);
            
            if (!$license) {
                // Check if it exists but is used
                $existingLicense = $this->licenseRepository->findOneBy(['licenseCode' => $licenseCode]);
                if ($existingLicense && $existingLicense->isUsed()) {
                    $this->addFlash('error', 'Cette licence est déjà utilisée.');
                } else {
                    $this->addFlash('error', 'Cette licence n\'existe pas.');
                }
                return $this->render('admin/users/assign_license.html.twig', [
                    'user' => $user,
                ]);
            }

            $license->assignToUser($user);
            $user->setLicense($license);
            $this->entityManager->flush();

            $this->addFlash('success', 'La licence a été attribuée à ' . $user->getUsername() . '.');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/assign_license.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/tournaments', name: 'admin_tournaments', methods: ['GET', 'POST'])]
    public function tournaments(Request $request): Response
    {
        $tournoi = new Tournoi();
        $form = $this->createForm(TournoiType::class, $tournoi);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->entityManager->persist($tournoi);
                $this->entityManager->flush();

                $this->addFlash('success', 'Le tournoi "' . $tournoi->getNom() . '" a été créé avec succès !');
                return $this->redirectToRoute('admin_tournaments');
            } else {
                $this->addFlash('error', 'Le formulaire contient des erreurs. Veuillez les corriger.');
            }
        }

        return $this->render('admin/tournaments/index.html.twig', [
            'tournaments' => $this->tournoiRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/settings', name: 'admin_settings')]
    public function settings(): Response
    {
        return $this->render('admin/settings/index.html.twig');
    }

    #[Route('/reports', name: 'admin_reports')]
    public function reports(): Response
    {
        return $this->render('admin/reports/index.html.twig', [
            'stats' => [
                'totalRevenue' => '125,450 DT',
                'monthlyRevenue' => '18,200 DT',
                'weeklyRevenue' => '4,500 DT',
                'dailyRevenue' => '850 DT',
                'totalTransactions' => 2847,
                'avgTransactionValue' => '44 DT',
            ],
            'revenueData' => [
                ['month' => 'Jan', 'revenue' => 12500],
                ['month' => 'Fév', 'revenue' => 15800],
                ['month' => 'Mar', 'revenue' => 18200],
                ['month' => 'Avr', 'revenue' => 16500],
                ['month' => 'Mai', 'revenue' => 19800],
                ['month' => 'Juin', 'revenue' => 22400],
            ],
            'topUsers' => [
                ['name' => 'ShadowSlayer99', 'spent' => '5,200 DT', 'tournaments' => 15],
                ['name' => 'ProGamer123', 'spent' => '4,800 DT', 'tournaments' => 12],
                ['name' => 'ElitePlayer', 'spent' => '3,900 DT', 'tournaments' => 10],
            ],
        ]);
    }

    #[Route('/shop', name: 'admin_shop')]
    public function shop(): Response
    {
        $skins = $this->skinRepository->findAll();
        $merch = $this->merchRepository->findAll();

        $items = [];

        foreach ($skins as $skin) {
            $items[] = [
                'id' => $skin->getId(),
                'name' => $skin->getName(),
                'game' => $skin->getGame() ? $skin->getGame()->getName() : 'N/A',
                'price' => $skin->getPrice() . ' DT', // Assuming currency
                'sales' => 0, // Placeholder
                'revenue' => '0 DT', // Placeholder
                'stock' => 'Illimité',
                'type' => 'Skin',
                'imageUrl' => $skin->getImageUrl(),
            ];
        }

        foreach ($merch as $m) {
            $items[] = [
                'id' => $m->getId(),
                'name' => $m->getName(),
                'game' => $m->getGame() ? $m->getGame()->getName() : 'N/A',
                'price' => $m->getPrice() . ' DT',
                'sales' => 0, // Placeholder
                'revenue' => '0 DT', // Placeholder
                'stock' => $m->getStock(),
                'type' => 'Merch',
                'imageUrl' => $m->getImageUrl(),
            ];
        }

        return $this->render('admin/shop/index.html.twig', [
            'items' => $items,
        ]);
    }

    #[Route('/shop/skin/add', name: 'admin_shop_skin_add', methods: ['GET', 'POST'])]
    public function addSkin(Request $request): Response
    {
        $skin = new Skin();
        $form = $this->createForm(SkinType::class, $skin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($skin);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le skin "' . $skin->getName() . '" a été ajouté avec succès !');
            return $this->redirectToRoute('admin_shop');
        }

        return $this->render('admin/shop/skin_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Ajouter un Skin'
        ]);
    }

    #[Route('/shop/skin/{id}/edit', name: 'admin_shop_skin_edit', methods: ['GET', 'POST'])]
    public function editSkin(Request $request, Skin $skin): Response
    {
        $form = $this->createForm(SkinType::class, $skin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Le skin "' . $skin->getName() . '" a été mis à jour avec succès !');
            return $this->redirectToRoute('admin_shop');
        }

        return $this->render('admin/shop/skin_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier : ' . $skin->getName()
        ]);
    }

    #[Route('/shop/skin/{id}/delete', name: 'admin_shop_skin_delete', methods: ['POST'])]
    public function deleteSkin(Skin $skin): Response
    {
        $this->entityManager->remove($skin);
        $this->entityManager->flush();

        $this->addFlash('success', 'Le skin a été supprimé avec succès.');
        return $this->redirectToRoute('admin_shop');
    }

    #[Route('/shop/merch/add', name: 'admin_shop_merch_add', methods: ['GET', 'POST'])]
    public function addMerch(Request $request): Response
    {
        $merch = new Merch();
        $form = $this->createForm(MerchType::class, $merch);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($merch);
            $this->entityManager->flush();

            $this->addFlash('success', 'L\'article "' . $merch->getName() . '" a été ajouté avec succès !');
            return $this->redirectToRoute('admin_shop');
        }

        return $this->render('admin/shop/merch_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Ajouter un Article (Merch)'
        ]);
    }

    #[Route('/shop/merch/{id}/edit', name: 'admin_shop_merch_edit', methods: ['GET', 'POST'])]
    public function editMerch(Request $request, Merch $merch): Response
    {
        $form = $this->createForm(MerchType::class, $merch);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'L\'article "' . $merch->getName() . '" a été mis à jour avec succès !');
            return $this->redirectToRoute('admin_shop');
        }

        return $this->render('admin/shop/merch_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier : ' . $merch->getName()
        ]);
    }

    #[Route('/shop/merch/{id}/delete', name: 'admin_shop_merch_delete', methods: ['POST'])]
    public function deleteMerch(Merch $merch): Response
    {
        $this->entityManager->remove($merch);
        $this->entityManager->flush();

        $this->addFlash('success', 'L\'article a été supprimé avec succès.');
        return $this->redirectToRoute('admin_shop');
    }

    #[Route('/games', name: 'admin_games', methods: ['GET', 'POST'])]
    public function games(Request $request): Response
    {
        $game = new Game();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($game);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le jeu "' . $game->getName() . '" a été ajouté avec succès !');
            return $this->redirectToRoute('admin_games');
        }

        return $this->render('admin/games/index.html.twig', [
            'games' => $this->gameRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/games/{id}/delete', name: 'admin_games_delete', methods: ['POST'])]
    public function deleteGame(Game $game): Response
    {
        $this->entityManager->remove($game);
        $this->entityManager->flush();

        $this->addFlash('success', 'Le jeu a été supprimé avec succès.');
        return $this->redirectToRoute('admin_games');
    }

    #[Route('/games/{id}/edit', name: 'admin_games_edit', methods: ['GET', 'POST'])]
    public function editGame(Request $request, Game $game): Response
    {
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Le jeu "' . $game->getName() . '" a été mis à jour avec succès !');
            return $this->redirectToRoute('admin_games');
        }

        return $this->render('admin/games/edit.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
        ]);
    }
}
