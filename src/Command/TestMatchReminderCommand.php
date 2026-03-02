<?php

namespace App\Command;

use App\Entity\MatchEntity;
use App\Entity\Team;
use App\Entity\Tournoi;
use App\Entity\User;
use App\Enum\MatchStatus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

#[AsCommand(
    name: 'app:test-match-reminder',
    description: 'Sends a realistic match reminder email using demo data.',
)]
class TestMatchReminderCommand extends Command
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly Environment $twig
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Carthage Arena - Realistic Email Test');

        $testRecipient = $io->ask('To which email address should I send the realistic test?', 'real.iheb2@gmail.com');

        $io->text('Preparing demo data and rendering template...');

        try {
            // 1. Create Demo Data (Mock objects without persistence)
            $player = new User();
            $player->setUsername('ProGamer_2024');
            $player->setEmail($testRecipient);

            $playerTeam = new Team();
            $playerTeam->setName('Carthage Warriors');

            $opponentTeam = new Team();
            $opponentTeam->setName('Desert Ninjas');

            $tournoi = new Tournoi();
            $tournoi->setNom('The International Carthage');

            $match = new MatchEntity();
            $match->setTournoi($tournoi);
            $match->setRound(3);
            $match->setStatus(MatchStatus::SCHEDULED);
            $match->setScheduledAt(new \DateTime('tomorrow 18:00:00'));

            // 2. Render the actual template
            $html = $this->twig->render('emails/match_reminder.html.twig', [
                'match' => $match,
                'player' => $player,
                'playerTeam' => $playerTeam,
                'opponentTeam' => $opponentTeam,
            ]);

            // 3. Send Email
            $email = (new Email())
                ->from('real.iheb2@gmail.com')
                ->to($testRecipient)
                ->subject('⚔ Rappel : Votre match "Carthage Warriors vs Desert Ninjas" est demain !')
                ->html($html);

            $this->mailer->send($email);

            $io->success('Realistic email sent! Please check: ' . $testRecipient);
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to send realistic email. Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
