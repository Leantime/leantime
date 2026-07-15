<?php

namespace Leantime\Command;

use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas as GoalcanvasRepo;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Import historical goal values from a CSV so a program three years into a
 * grant doesn't have to wait until year 5 to see an arc. Rows land in
 * `zp_goal_history` in exactly the same shape as the nightly capture
 * (`itemId`, `value`, `userId`, `dateRecorded`) — a value is a value; a
 * consumer cannot tell the difference and shouldn't need to.
 */
#[AsCommand(
    name: 'goals:backfillHistory',
    description: 'Import historical goal values from a CSV into zp_goal_history.',
)]
class BackfillGoalHistoryCommand extends Command
{
    protected function configure(): void
    {
        parent::configure();

        $this->addOption('file', null, InputOption::VALUE_REQUIRED, 'Path to a CSV with header row: itemId,value,dateRecorded[,userId]')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Parse and validate the CSV without inserting.');
    }

    /**
     * @throws BindingResolutionException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ! defined('BASE_URL') && define('BASE_URL', '');
        ! defined('CURRENT_URL') && define('CURRENT_URL', '');

        $io = new SymfonyStyle($input, $output);
        $path = $input->getOption('file');
        $dryRun = (bool) $input->getOption('dry-run');

        if ($path === null || $path === '') {
            $io->error('Missing --file <path.csv>');

            return Command::INVALID;
        }
        if (! is_file($path) || ! is_readable($path)) {
            $io->error("File not found or unreadable: {$path}");

            return Command::INVALID;
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            $io->error("Could not open {$path}");

            return Command::FAILURE;
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            $io->error('CSV is empty.');

            return Command::INVALID;
        }
        $header = array_map(static fn ($h) => is_string($h) ? strtolower(trim($h)) : '', $header);

        // Match by header name so column order doesn't matter — a spreadsheet
        // exported from anywhere can be fed in without hand-editing.
        $idxItem = array_search('itemid', $header, true);
        $idxValue = array_search('value', $header, true);
        $idxDate = array_search('daterecorded', $header, true);
        $idxUser = array_search('userid', $header, true);

        if ($idxItem === false || $idxValue === false || $idxDate === false) {
            fclose($handle);
            $io->error('CSV header must include: itemId, value, dateRecorded (userId optional).');

            return Command::INVALID;
        }

        $rows = [];
        $errors = [];
        $lineNo = 1;
        while (($record = fgetcsv($handle)) !== false) {
            $lineNo++;
            if ($record === [null] || $record === []) {
                continue;
            }

            $itemId = (int) ($record[$idxItem] ?? 0);
            $rawValue = $record[$idxValue] ?? '';
            $rawDate = trim((string) ($record[$idxDate] ?? ''));

            if ($itemId <= 0) {
                $errors[] = "line {$lineNo}: itemId missing or invalid";

                continue;
            }
            if ($rawValue === '' || ! is_numeric($rawValue)) {
                $errors[] = "line {$lineNo}: value missing or not numeric";

                continue;
            }
            $ts = strtotime($rawDate);
            if ($ts === false) {
                $errors[] = "line {$lineNo}: unparseable dateRecorded '{$rawDate}'";

                continue;
            }

            $rows[] = [
                'itemId' => $itemId,
                'value' => (float) $rawValue,
                'userId' => ($idxUser !== false && isset($record[$idxUser]) && $record[$idxUser] !== '') ? (int) $record[$idxUser] : null,
                'dateRecorded' => date('Y-m-d H:i:s', $ts),
            ];
        }
        fclose($handle);

        if ($errors !== []) {
            $io->section('Validation errors');
            foreach ($errors as $e) {
                $io->writeln(' - '.$e);
            }
            $io->error(sprintf('%d row(s) invalid; nothing written.', count($errors)));

            return Command::INVALID;
        }

        if ($rows === []) {
            $io->warning('No data rows after header — nothing to import.');

            return Command::SUCCESS;
        }

        if ($dryRun) {
            $io->success(sprintf('Dry run: %d row(s) would be inserted.', count($rows)));

            return Command::SUCCESS;
        }

        try {
            $repo = app()->make(GoalcanvasRepo::class);
            $written = $repo->insertGoalHistoryRows($rows);
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success(sprintf('Imported %d row(s) into zp_goal_history.', $written));

        return Command::SUCCESS;
    }
}
