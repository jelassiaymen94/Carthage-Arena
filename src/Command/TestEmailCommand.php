<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:test-email',
    description: 'Sends a test email to verify MAILER_DSN configuration.',
)]
class TestEmailCommand extends Command
{
    public function __construct(
        private readonly MailerInterface $mailer
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Carthage Arena - Email Test');

        $testRecipient = $io->ask('To which email address should I send the test mail?', 'real.iheb2@gmail.com');

        $io->text('Attempting to send a test email to: ' . $testRecipient);

        try {
            $email = (new Email())
                ->from('real.iheb2@gmail.com')
                ->to($testRecipient)
                ->subject('Carthage Arena - Test Email')
                ->text('This is a test email from the Carthage Arena Symfony application. If you receive this, your MAILER_DSN is correctly configured!')
                ->html('<p>This is a test email from the <strong>Carthage Arena</strong> Symfony application.</p><p>If you receive this, your <code>MAILER_DSN</code> is correctly configured!</p>');

            $this->mailer->send($email);

            $io->success('Email sent successfully! Please check the inbox of ' . $testRecipient);
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to send email. Error: ' . $e->getMessage());
            $io->note('Ensure your MAILER_DSN in .env is correct and that you are using a Gmail App Password if using Gmail.');
            return Command::FAILURE;
        }
    }
}
