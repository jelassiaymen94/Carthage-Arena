<?php

namespace App\Scheduler;

use App\Repository\MatchRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;
use Twig\Environment;

#[AsMessageHandler]
class SendMatchRemindersHandler
{
    public function __construct(
        private readonly MatchRepository $matchRepository,
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
    ) {
    }

    public function __invoke(SendMatchRemindersMessage $message): void
    {
        $matches = $this->matchRepository->findScheduledForTomorrow();

        if (empty($matches)) {
            return;
        }

        foreach ($matches as $match) {
            $team1 = $match->getTeam1();
            $team2 = $match->getTeam2();

            if (!$team1 || !$team2) {
                continue;
            }

            $recipients = [];

            foreach ($team1->getMembers() as $membership) {
                $player = $membership->getPlayer();
                if ($player && $player->getEmail()) {
                    $recipients[] = [
                        'player' => $player,
                        'playerTeam' => $team1,
                        'opponentTeam' => $team2,
                    ];
                }
            }

            foreach ($team2->getMembers() as $membership) {
                $player = $membership->getPlayer();
                if ($player && $player->getEmail()) {
                    $recipients[] = [
                        'player' => $player,
                        'playerTeam' => $team2,
                        'opponentTeam' => $team1,
                    ];
                }
            }

            foreach ($recipients as $recipient) {
                try {
                    $html = $this->twig->render('emails/match_reminder.html.twig', [
                        'match' => $match,
                        'player' => $recipient['player'],
                        'playerTeam' => $recipient['playerTeam'],
                        'opponentTeam' => $recipient['opponentTeam'],
                    ]);

                    $email = (new Email())
                        ->from('real.iheb2@gmail.com')
                        ->to($recipient['player']->getEmail())
                        ->subject(sprintf(
                            '⚔ Rappel : Votre match "%s vs %s" est demain !',
                            $recipient['playerTeam']->getName(),
                            $recipient['opponentTeam']->getName()
                        ))
                        ->html($html);

                    $this->mailer->send($email);
                } catch (\Throwable) {
                    // Log failure silently; individual failures don't abort the batch
                }
            }
        }
    }
}
