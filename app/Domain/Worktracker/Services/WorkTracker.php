<?php

namespace Leantime\Domain\Worktracker\Services;

use Illuminate\Support\Facades\Log;
use Leantime\Core\Configuration\Environment;
use Leantime\Domain\Worktracker\Repositories\WorkTracker as WorkTrackerRepository;

class WorkTracker
{
    /**
     * Max allowed base64 payload size: ~4 MB decoded ≈ 5.5 MB base64.
     * This limits screenshot upload abuse without blocking normal screen captures.
     */
    private const MAX_SCREENSHOT_BYTES = 5_500_000;

    /**
     * Screenshot store path relative to userfiles root.
     */
    private const SCREENSHOT_DIR = 'worktracker/screenshots';

    private WorkTrackerRepository $repo;

    private Environment $config;

    public function __construct(WorkTrackerRepository $repo, Environment $config)
    {
        $this->repo   = $repo;
        $this->config = $config;
    }

    // ─────────────────────────────────────────────────────────────
    //  Session lifecycle
    // ─────────────────────────────────────────────────────────────

    /**
     * Start a new work session for the given user.
     *
     * @param  int     $userId
     * @param  string  $base64Screenshot  Raw base64 string (no data-URI prefix required but handled)
     * @return array   ['success' => bool, 'session_id' => int|null, 'message' => string]
     */
    public function startSession(int $userId, string $base64Screenshot): array
    {
        // Prevent duplicate running sessions
        $existing = $this->repo->getActiveSession($userId);
        if ($existing !== false) {
            return [
                'success'    => false,
                'session_id' => (int) $existing->id,
                'message'    => 'A session is already running.',
            ];
        }

        $screenshotPath = '';
        if (! empty($base64Screenshot)) {
            $result = $this->saveScreenshot($userId, 0, 'start', $base64Screenshot);
            if ($result['success']) {
                $screenshotPath = $result['path'];
            } else {
                Log::warning('WorkTracker: screenshot save failed on start', ['userId' => $userId, 'error' => $result['message']]);
            }
        }

        $sessionId = $this->repo->createSession($userId, $screenshotPath);

        // If we saved the screenshot with a placeholder session ID (0), rename it with the real ID
        if (! empty($screenshotPath) && str_contains($screenshotPath, '_0_start')) {
            $finalPath = $this->renameScreenshot($screenshotPath, $userId, $sessionId, 'start');
            if ($finalPath) {
                // Update the just-created row with the renamed path
                app(\Leantime\Core\Db\Db::class)->getConnection()
                    ->table('zp_work_sessions')
                    ->where('id', $sessionId)
                    ->update(['start_screenshot' => $finalPath]);
                $screenshotPath = $finalPath;
            }
        }

        return [
            'success'    => true,
            'session_id' => $sessionId,
            'message'    => 'Session started.',
        ];
    }

    /**
     * Cancel an orphaned running session WITHOUT a screenshot.
     *
     * Used when an employee closes their browser without clicking Stop, then
     * comes back and needs to clear the stuck session before starting a new one.
     * Closes the session, calculates duration up to now, and writes no end_screenshot.
     *
     * @return array ['success' => bool, 'duration' => int|null, 'message' => string]
     */
    public function cancelSession(int $sessionId, int $userId): array
    {
        $session = $this->repo->getSession($sessionId, $userId);
        if (! $session) {
            return ['success' => false, 'duration' => null, 'message' => 'Session not found.'];
        }
        if ($session->status !== 'running') {
            return ['success' => false, 'duration' => null, 'message' => 'Session is not running.'];
        }

        $closed = $this->repo->closeSession($sessionId, $userId, '');
        if (! $closed) {
            return ['success' => false, 'duration' => null, 'message' => 'Could not close session.'];
        }

        $duration = (new \DateTime())->getTimestamp() - (new \DateTime($session->start_time))->getTimestamp();

        Log::info('WorkTracker: session cancelled (orphaned/no end screenshot)', [
            'sessionId' => $sessionId,
            'userId'    => $userId,
            'duration'  => $duration,
        ]);

        return ['success' => true, 'duration' => $duration, 'message' => 'Session cancelled.'];
    }

    /**
     * Stop the specified running session.
     *
     * @param  int     $sessionId
     * @param  int     $userId
     * @param  string  $base64Screenshot
     * @return array   ['success' => bool, 'duration' => int|null, 'message' => string]
     */
    public function stopSession(int $sessionId, int $userId, string $base64Screenshot): array
    {
        $session = $this->repo->getSession($sessionId, $userId);

        if (! $session) {
            return ['success' => false, 'duration' => null, 'message' => 'Session not found or already completed.'];
        }

        if ($session->status !== 'running') {
            return ['success' => false, 'duration' => null, 'message' => 'Session is not running.'];
        }

        $screenshotPath = '';
        if (! empty($base64Screenshot)) {
            $result = $this->saveScreenshot($userId, $sessionId, 'end', $base64Screenshot);
            if ($result['success']) {
                $screenshotPath = $result['path'];
            } else {
                Log::warning('WorkTracker: screenshot save failed on stop', ['sessionId' => $sessionId, 'error' => $result['message']]);
            }
        }

        $closed = $this->repo->closeSession($sessionId, $userId, $screenshotPath);

        if (! $closed) {
            return ['success' => false, 'duration' => null, 'message' => 'Could not close session.'];
        }

        $startTime = new \DateTime($session->start_time);
        $duration  = (new \DateTime())->getTimestamp() - $startTime->getTimestamp();

        return [
            'success'  => true,
            'duration' => $duration,
            'message'  => 'Session completed.',
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  Status / dashboard queries
    // ─────────────────────────────────────────────────────────────

    /**
     * Returns the current timer state for the navbar widget.
     *
     * @return array{running: bool, session_id: int|null, elapsed_seconds: int, start_time: string|null}
     */
    public function getTimerStatus(int $userId): array
    {
        $session = $this->repo->getActiveSession($userId);

        if (! $session) {
            return ['running' => false, 'session_id' => null, 'elapsed_seconds' => 0, 'start_time' => null];
        }

        $elapsed = (new \DateTime())->getTimestamp() - (new \DateTime($session->start_time))->getTimestamp();

        return [
            'running'         => true,
            'session_id'      => (int) $session->id,
            'elapsed_seconds' => $elapsed,
            'start_time'      => $session->start_time,
        ];
    }

    /**
     * Data bundle for the employee dashboard.
     */
    public function getEmployeeDashboard(int $userId): array
    {
        $today        = now()->toDateString();
        $activeSession = $this->repo->getActiveSession($userId);

        $elapsed = 0;
        if ($activeSession) {
            $elapsed = (new \DateTime())->getTimestamp() - (new \DateTime($activeSession->start_time))->getTimestamp();
        }

        $sessions = $this->repo->getUserSessions($userId, 20, 0);
        $totalCount = $this->repo->countUserSessions($userId);

        return [
            'active_session'  => $activeSession,
            'elapsed_seconds' => $elapsed,
            'today_total'     => $this->repo->getDayTotal($userId, $today),
            'week_total'      => $this->repo->getWeekTotal($userId),
            'sessions'        => $sessions,
            'total_count'     => $totalCount,
        ];
    }

    /**
     * Data bundle for the admin dashboard.
     */
    public function getAdminDashboard(int $page = 1, int $perPage = 50): array
    {
        $offset   = ($page - 1) * $perPage;
        $sessions = $this->repo->getAllSessions($perPage, $offset);
        $total    = $this->repo->countAllSessions();

        return [
            'sessions'            => $sessions,
            'total_count'         => $total,
            'page'                => $page,
            'per_page'            => $perPage,
            'total_pages'         => (int) ceil($total / $perPage),
            'active_now'          => $this->repo->getActiveCount(),
            'today_grand_total'   => $this->repo->getTodayGrandTotal(),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Format a duration in seconds to "HH:MM:SS".
     */
    public static function formatDuration(int $seconds): string
    {
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }

    /**
     * Decode, validate, and persist a base64 screenshot to disk.
     *
     * @return array{success: bool, path: string, message: string}
     */
    private function saveScreenshot(int $userId, int $sessionId, string $type, string $base64): array
    {
        // Strip optional data-URI header: "data:image/png;base64,..."
        $base64 = preg_replace('/^data:image\/[a-z]+;base64,/i', '', $base64);

        if (strlen($base64) > self::MAX_SCREENSHOT_BYTES) {
            return ['success' => false, 'path' => '', 'message' => 'Screenshot payload exceeds size limit.'];
        }

        $binary = base64_decode($base64, true);
        if ($binary === false) {
            return ['success' => false, 'path' => '', 'message' => 'Invalid base64 data.'];
        }

        // Validate it is actually an image by checking magic bytes
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->buffer($binary);
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return ['success' => false, 'path' => '', 'message' => 'Only JPEG, PNG and WebP screenshots are accepted.'];
        }

        $ext     = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            default      => 'png',
        };

        $dir  = $this->screenshotDir();
        $file = "{$userId}_{$sessionId}_{$type}.{$ext}";
        $full = $dir . DIRECTORY_SEPARATOR . $file;

        if (file_put_contents($full, $binary) === false) {
            return ['success' => false, 'path' => '', 'message' => 'Could not write screenshot to disk.'];
        }

        return ['success' => true, 'path' => self::SCREENSHOT_DIR . '/' . $file, 'message' => 'OK'];
    }

    /**
     * Rename a screenshot file when the real session ID is known post-insert.
     */
    private function renameScreenshot(string $oldRelPath, int $userId, int $sessionId, string $type): string|false
    {
        $base    = $this->screenshotDir();
        $oldFile = $base . DIRECTORY_SEPARATOR . basename($oldRelPath);

        if (! file_exists($oldFile)) {
            return false;
        }

        $ext     = pathinfo($oldFile, PATHINFO_EXTENSION);
        $newFile = "{$userId}_{$sessionId}_{$type}.{$ext}";
        $newFull = $base . DIRECTORY_SEPARATOR . $newFile;

        if (! rename($oldFile, $newFull)) {
            return false;
        }

        return self::SCREENSHOT_DIR . '/' . $newFile;
    }

    /**
     * Return (and create if needed) the absolute path to the screenshots directory.
     */
    private function screenshotDir(): string
    {
        $dir = base_path('userfiles') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, self::SCREENSHOT_DIR);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir;
    }

    /**
     * Build the public URL for a stored screenshot.
     * Routed through the auth-gated Screenshot controller so cross-user
     * access is denied (employees see only their own; manager+ see all).
     *
     * @param  int     $sessionId  DB id of the work session
     * @param  string  $type       'start' | 'end'
     * @param  string  $relative   The stored relative path (used only to confirm a file exists)
     */
    public function screenshotUrl(int $sessionId, string $type, string $relative): string
    {
        if (empty($relative) || $sessionId <= 0 || ! in_array($type, ['start', 'end'], true)) {
            return '';
        }

        return rtrim(BASE_URL, '/') . '/worktracker/screenshot?session_id=' . $sessionId . '&type=' . $type;
    }
}
