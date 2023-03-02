<?php


namespace leantime\command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use leantime\core\mailer;

class testEmailCommand extends Command
{
  protected static $defaultName = 'email:test';
  protected static $defaultDescription = 'Sends an email to test system configuration';

  protected function configure()
  {
    parent::configure();
    $this->addOption('address', null, InputOption::VALUE_REQUIRED, "Recipient email address");
  }

  /**
   * Execute the command
   *
   * @param  InputInterface  $input
   * @param  OutputInterface $output
   * @return int 0 if everything went fine, or an exit code.
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  { 
    define('BASE_URL', "");
    define('CURRENT_URL', "");
    $io = new SymfonyStyle($input, $output);
    
    $address = $input->getOption('address');

    if($address == '') {
      $io->error("address parameter needs to be set");
      return Command::INVALID;
    } 

    $config = \leantime\core\environment::getInstance();
    
    // force debug output from mailer subsystem
    $config->debug = 1;
    $mailer = new Mailer();

    $io = new SymfonyStyle($input, $output);
    $io->writeln('Sending a test email using current configuration');

    $mailer = new Mailer();
    $mailer->setSubject('Leantime email test');
    $mailer->setHtml('This is a test of the leantime mailer configuration. If you have received this email, then the mail configuration is correct.');
    $mailer->sendMail(Array($input->getOption('address')), 'Command-line test');

    return Command::SUCCESS;
  }
}
