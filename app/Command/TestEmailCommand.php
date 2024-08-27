<?php

namespace Leantime\Command;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Mailer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class TestEmailCommand
 *
 * This class represents a command that sends an email to test the system configuration.
 */
#[AsCommand(
    name: 'email:test',
    description: 'Sends an email to test system configuration',
)]
class TestEmailCommand extends Command
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this->addOption('address', null, InputOption::VALUE_REQUIRED, "Recipient email address");
    }

    /**
     * Execute the command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int 0 if everything went fine, or an exit code.
     * @throws BindingResolutionException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Depending on the entry point, the constants may not be defined
        !defined('BASE_URL') && define('BASE_URL', "");
        !defined('CURRENT_URL') && define('CURRENT_URL', "");

        $io = new SymfonyStyle($input, $output);

        $address = $input->getOption('address');
        if ($address == '') {
            $io->error("address parameter needs to be set");
            return Command::INVALID;
        }

        $config = app()->make(Environment::class);

        // Force debug output from the mailer subsystem
        $config->debug = 1;

        $io = new SymfonyStyle($input, $output);
        $io->writeln('Sending a test email using current configuration');

        $mailer = app()->make(Mailer::class);
        $mailer->setSubject('Leantime email test');
        $mailer->setHtml('This is a test of the leantime mailer configuration. If you have received this email, then the mail configuration is correct.', true);
        $mailer->sendMail(array($input->getOption('address')), 'Command-line test');

        return Command::SUCCESS;
    }
}
