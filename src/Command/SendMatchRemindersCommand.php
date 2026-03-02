<?php

namespace App\Command;

use App\Repository\MatchRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

#[AsCommand(
    name: 'app:send-match-reminders',
    description: 'Send email reminders to all players whose match is scheduled for tomorrow.',
)]
class SendMatchRemindersCommand extends Command
{
    public function __construct(
        private readonly MatchRepository $matchRepository,
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Carthage Arena — Match Reminders');

        $matches = $this->matchRepository->findScheduledForTomorrow();

        if (empty($matches)) {
            $io->info('No matches scheduled for tomorrow. Nothing to do.');
            return Command::SUCCESS;
        }

        $io->info(sprintf('Found %d match(es) scheduled for tomorrow.', count($matches)));

        $sent = 0;
        $skipped = 0;

        foreach ($matches as $match) {
            $team1 = $match->getTeam1();
            $team2 = $match->getTeam2();

            if (!$team1 || !$team2) {
                $io->warning(sprintf('Match #%s is missing one or both teams — skipping.', $match->getId()));
                $skipped++;
                continue;
            }

            // Collect all unique players from both teams
            $recipients = []; // [$player => $playerTeam, $opponentTeam]

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

                    $io->writeln(sprintf(
                        '  ✓ Email sent to <info>%s</info> (%s)',
                        $recipient['player']->getEmail(),
                        $recipient['player']->getUsername()
                    ));
                    $sent++;
                } catch (\Throwable $e) {
                    $io->error(sprintf(
                        'Failed to send to %s: %s',
                        $recipient['player']->getEmail(),
                        $e->getMessage()
                    ));
                    $skipped++;
                }
            }
        }

        $io->success(sprintf(
            'Done! %d email(s) sent, %d skipped.',
            $sent,
            $skipped
        ));

        return Command::SUCCESS;
    }
}
