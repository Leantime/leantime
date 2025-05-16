<?php

namespace Leantime\Command;

use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupOrphanedFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:cleanup {--dry-run : Run without actually deleting files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up orphaned files that are not referenced in the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(FilesystemManager $filesystemManager)
    {
        $this->info('Starting orphaned files cleanup...');

        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->warn('Running in dry-run mode. No files will be deleted.');
        }

        // Get all files from database
        $dbFiles = DB::table('zp_file')->select('encName', 'extension')->get();
        $dbFileNames = [];

        foreach ($dbFiles as $file) {
            $dbFileNames[] = $file->encName.'.'.$file->extension;
        }

        $this->info('Found '.count($dbFileNames).' files in database.');

        // Process local storage
        $this->processStorage($filesystemManager, 'local', $dbFileNames, $dryRun);

        // Process public storage
        $this->processStorage($filesystemManager, 'public', $dbFileNames, $dryRun);

        // Process S3 storage if configured
        if (config('filesystems.default') === 's3') {
            $this->processStorage($filesystemManager, 's3', $dbFileNames, $dryRun);
        }

        $this->info('Orphaned files cleanup completed.');

        return 0;
    }

    /**
     * Process a specific storage disk to find and delete orphaned files
     *
     * @return void
     */
    private function processStorage(FilesystemManager $filesystemManager, string $disk, array $dbFileNames, bool $dryRun)
    {
        $this->info("Processing {$disk} storage...");

        try {
            $storage = $filesystemManager->disk($disk);
            $files = $storage->files();

            $orphanedFiles = array_filter($files, function ($file) use ($dbFileNames) {
                $fileName = basename($file);

                return ! in_array($fileName, $dbFileNames);
            });

            $this->info('Found '.count($orphanedFiles)." orphaned files in {$disk} storage.");

            $deletedCount = 0;
            foreach ($orphanedFiles as $file) {
                // Skip system files and directories
                if (in_array(basename($file), ['.gitignore', '.htaccess', 'index.html'])) {
                    continue;
                }

                $this->line("Processing: {$file}");

                // Check file age (only delete files older than 24 hours)
                try {
                    $lastModified = $storage->lastModified($file);
                    $fileAge = time() - $lastModified;

                    if ($fileAge < 86400) { // 24 hours
                        $this->warn("Skipping {$file} - file is less than 24 hours old");

                        continue;
                    }

                    if (! $dryRun) {
                        if ($storage->delete($file)) {
                            $this->info("Deleted: {$file}");
                            $deletedCount++;
                        } else {
                            $this->error("Failed to delete: {$file}");
                        }
                    } else {
                        $this->info("Would delete: {$file} (dry run)");
                        $deletedCount++;
                    }
                } catch (\Exception $e) {
                    $this->error("Error processing {$file}: ".$e->getMessage());
                    Log::error('Error in orphaned files cleanup: '.$e->getMessage(), [
                        'file' => $file,
                        'disk' => $disk,
                        'exception' => $e,
                    ]);
                }
            }

            $actionText = $dryRun ? 'Would have deleted' : 'Deleted';
            $this->info("{$actionText} {$deletedCount} orphaned files from {$disk} storage.");

        } catch (\Exception $e) {
            $this->error("Error accessing {$disk} storage: ".$e->getMessage());
            Log::error('Error accessing storage in orphaned files cleanup: '.$e->getMessage(), [
                'disk' => $disk,
                'exception' => $e,
            ]);
        }
    }
}
