<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\TeamMembership;
use App\Entity\User;
use App\Enum\TeamRole;
use App\Enum\TeamStatus;
use App\Form\TeamCreateType;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\ByteString;

class TeamController extends AbstractController
{
    #[Route('/equipe', name: 'app_team')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Find if user has a team membership
        $membership = null;
        if (!$user->getTeamMemberships()->isEmpty()) {
            $membership = $user->getTeamMemberships()->first();
        }

        if (!$membership) {
            return $this->render('team/no_team.html.twig');
        }

        $team = $membership->getTeam();

        // Don't show disbanded/inactive teams (or handle them differently)
        if ($team->getStatus() !== TeamStatus::ACTIVE) {
            return $this->render('team/no_team.html.twig');
        }

        // Map team members to view data
        $membersData = [];
        foreach ($team->getMembers() as $mem) {
            $memberUser = $mem->getPlayer();
            $memberProfile = $memberUser->getProfile();
            $membersData[] = [
                'id' => $mem->getId(), // Needed for kick action
                'name' => $memberUser->getUsername(),
                'role' => match ($mem->getRole()) {
                    TeamRole::CAPTAIN => 'Leader',
                    TeamRole::CO_CAPTAIN => 'Co-Leader',
                    default => 'Membre'
                },
                'avatar' => $memberProfile && $memberProfile->getAvatarUrl() ? '/uploads/avatars/' . $memberProfile->getAvatarUrl() : 'https://i.pravatar.cc/150?u=' . $memberUser->getId(),
                'status' => 'offline', // Placeholder
                'rank' => 'Unranked' // Placeholder
            ];
        }

        $captainUser = $team->getCaptain();

        return $this->render('team/index.html.twig', [
            'team' => [
                'name' => $team->getName(),
                'tag' => $team->getTag(),
                'logo' => 'https://via.placeholder.com/150/FF0022/FFFFFF?text=' . $team->getTag(), // Placeholder for now
                'created_at' => $team->getCreatedAt()->format('d M Y'),
                'description' => $team->getDescription(),
                'leader' => $captainUser->getUsername(),
                'level' => 1, // Placeholder
                'wins' => 0, // Placeholder
                'losses' => 0, // Placeholder
                'tournaments_won' => 0, // Placeholder
                'members' => $membersData,
                'invites' => $team->getInviteCode(),
                'upcoming_tournaments' => []
            ],
            'is_leader' => $membership->getRole() === TeamRole::CAPTAIN,
        ]);
    }

    #[Route('/equipe/creer', name: 'app_team_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->getTeamMemberships()->isEmpty()) {
            $this->addFlash('error', 'Vous avez déjà une équipe.');
            return $this->redirectToRoute('app_team');
        }

        $team = new Team();
        $form = $this->createForm(TeamCreateType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $team->setCaptain($user);
            $team->setInviteCode(strtoupper(ByteString::fromRandom(8)->toString()));

            $membership = new TeamMembership();
            $membership->setTeam($team);
            $membership->setPlayer($user);
            $membership->setRole(TeamRole::CAPTAIN);

            $entityManager->persist($team);
            $entityManager->persist($membership);
            $entityManager->flush();

            $this->addFlash('success', 'Équipe créée avec succès !');
            return $this->redirectToRoute('app_team');
        }

        return $this->render('team/create.html.twig', [
            'teamForm' => $form->createView(),
        ]);
    }

    #[Route('/equipe/modifier', name: 'app_team_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $membership = $user->getTeamMemberships()->first();

        if (!$membership || $membership->getRole() !== TeamRole::CAPTAIN) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('app_team');
        }

        $team = $membership->getTeam();
        $form = $this->createForm(\App\Form\TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Équipe modifiée avec succès !');
            return $this->redirectToRoute('app_team');
        }

        return $this->render('team/edit.html.twig', [
            'form' => $form->createView(),
            'team' => $team,
        ]);
    }

    #[Route('/equipe/rejoindre', name: 'app_team_join', methods: ['GET', 'POST'])]
    public function join(Request $request, EntityManagerInterface $entityManager, TeamRepository $teamRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->getTeamMemberships()->isEmpty()) {
            $this->addFlash('error', 'Vous avez déjà une équipe.');
            return $this->redirectToRoute('app_team');
        }

        if ($request->isMethod('POST')) {
            $submittedToken = $request->request->get('_csrf_token');
            if (!$this->isCsrfTokenValid('join_team', $submittedToken)) {
                $this->addFlash('error', 'Token CSRF invalide.');
                return $this->redirectToRoute('app_team_join');
            }

            $code = strtoupper(trim($request->request->get('invite_code', '')));

            if (empty($code)) {
                $this->addFlash('error', 'Le code d\'invitation est obligatoire.');
                return $this->redirectToRoute('app_team_join');
            }

            $team = $teamRepository->findOneByInviteCode($code);

            if (!$team || $team->getStatus() !== TeamStatus::ACTIVE) {
                $this->addFlash('error', 'Code d\'invitation invalide ou équipe inactive.');
                return $this->redirectToRoute('app_team_join');
            }

            if ($team->getMembers()->count() >= 8) {
                $this->addFlash('error', 'Cette équipe est au complet.');
                return $this->redirectToRoute('app_team_join');
            }

            $membership = new TeamMembership();
            $membership->setTeam($team);
            $membership->setPlayer($user);
            $membership->setRole(TeamRole::MEMBER);

            $entityManager->persist($membership);
            $entityManager->flush();

            $this->addFlash('success', 'Vous avez rejoint l\'équipe ' . $team->getName() . ' !');
            return $this->redirectToRoute('app_team');
        }

        return $this->render('team/join.html.twig');
    }

    #[Route('/equipe/quitter', name: 'app_team_leave', methods: ['POST'])]
    public function leave(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user->getTeamMemberships()->isEmpty()) {
            return $this->redirectToRoute('app_team');
        }

        $submittedToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('leave_team', $submittedToken)) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_team');
        }

        $membership = $user->getTeamMemberships()->first();
        $team = $membership->getTeam();

        if ($membership->getRole() === TeamRole::CAPTAIN) {
            if ($team->getMembers()->count() > 1) {
                // Assign new captain (next joined member)
                $team->removeMember($membership);
                $newCaptainMembership = $team->getMembers()->first();
                $newCaptainMembership->setRole(TeamRole::CAPTAIN);
                $team->setCaptain($newCaptainMembership->getPlayer());

                $entityManager->remove($membership);
                $this->addFlash('warning', 'Vous avez quitté l\'équipe. ' . $newCaptainMembership->getPlayer()->getUsername() . ' est le nouveau leader.');
            } else {
                // Last member, disband
                $team->setStatus(TeamStatus::DISBANDED);
                $entityManager->remove($membership);
                $this->addFlash('warning', 'Équipe dissoute.');
            }
        } else {
            $entityManager->remove($membership);
            $this->addFlash('success', 'Vous avez quitté l\'équipe.');
        }

        $entityManager->flush();
        return $this->redirectToRoute('app_team');
    }

    #[Route('/equipe/dissoudre', name: 'app_team_disband', methods: ['POST'])]
    public function disband(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $membership = $user->getTeamMemberships()->first();

        if (!$membership || $membership->getRole() !== TeamRole::CAPTAIN) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('app_team');
        }

        $submittedToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('disband_team', $submittedToken)) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_team');
        }

        $team = $membership->getTeam();
        $team->setStatus(TeamStatus::DISBANDED);

        // Remove all memberships
        foreach ($team->getMembers() as $mem) {
            $entityManager->remove($mem);
        }

        $entityManager->flush();
        $this->addFlash('success', 'Équipe dissoute.');
        return $this->redirectToRoute('app_team');
    }

    #[Route('/equipe/nouveau-code', name: 'app_team_regenerate_invite', methods: ['POST'])]
    public function regenerateInvite(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $membership = $user->getTeamMemberships()->first();

        if (!$membership || $membership->getRole() !== TeamRole::CAPTAIN) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('app_team');
        }

        $submittedToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('regenerate_invite', $submittedToken)) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_team');
        }

        $team = $membership->getTeam();
        $team->setInviteCode(strtoupper(ByteString::fromRandom(8)->toString()));

        $entityManager->flush();
        $this->addFlash('success', 'Nouveau code d\'invitation généré.');
        return $this->redirectToRoute('app_team');
    }

    // Optional: Kick member
    #[Route('/equipe/expulser/{id}', name: 'app_team_kick', methods: ['POST'])]
    public function kick(string $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $captainMembership = $user->getTeamMemberships()->first();

        if (!$captainMembership || $captainMembership->getRole() !== TeamRole::CAPTAIN) {
            return $this->redirectToRoute('app_team');
        }

        $submittedToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('kick_member', $submittedToken)) {
            return $this->redirectToRoute('app_team');
        }

        $targetMembership = $entityManager->getRepository(TeamMembership::class)->find($id);
        if (!$targetMembership || $targetMembership->getTeam() !== $captainMembership->getTeam()) {
            return $this->redirectToRoute('app_team');
        }

        if ($targetMembership === $captainMembership) {
            $this->addFlash('error', 'Vous ne pouvez pas vous expulser vous-même.');
            return $this->redirectToRoute('app_team');
        }

        $entityManager->remove($targetMembership);
        $entityManager->flush();

        $this->addFlash('success', 'Membre expulsé.');
        return $this->redirectToRoute('app_team');
    }
}
