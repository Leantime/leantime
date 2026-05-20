<?php

namespace Leantime\Domain\Worktracker\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Worktracker\Repositories\WorkTracker as WorkTrackerRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Auth-gated screenshot file streamer.
 *
 * URL:   GET /worktracker/screenshot?session_id={id}&type={start|end}
 * Auth:  Employee → only their own session
 *        Manager/Admin/Owner → any session
 *
 * Required because screenshots live under userfiles/ (private)
 * and must not be guessable cross-user via direct path.
 */
class Screenshot extends Controller
{
    private WorkTrackerRepository $repo;

    public function init(WorkTrackerRepository $repo): void
    {
        $this->repo = $repo;
    }

    public function run(): Response
    {
        Auth::authOrRedirect([Roles::$editor, Roles::$manager, Roles::$admin, Roles::$owner], true);

        $sessionId = isset($_GET['session_id']) ? (int) $_GET['session_id'] : 0;
        $type      = $_GET['type'] ?? '';

        if ($sessionId <= 0 || ! in_array($type, ['start', 'end'], true)) {
            return new Response('Bad request', 400);
        }

        $currentUserId = (int) session('userdata.id');
        $currentRole   = session('userdata.role') ?? '';
        $isPrivileged  = in_array($currentRole, [Roles::$manager, Roles::$admin, Roles::$owner], true);

        $session = $isPrivileged
            ? $this->findAnyById($sessionId)
            : $this->repo->getSession($sessionId, $currentUserId);

        if (! $session) {
            return new Response('Not found', 404);
        }

        $relative = $type === 'start'
            ? ($session->start_screenshot ?? '')
            : ($session->end_screenshot ?? '');

        if (empty($relative)) {
            return new Response('No screenshot', 404);
        }

        $fullPath = $this->resolvePath($relative);

        if ($fullPath === null || ! is_file($fullPath)) {
            return new Response('File missing', 404);
        }

        $response = new BinaryFileResponse($fullPath);
        $response->headers->set('Content-Type', $this->guessMime($fullPath));
        $response->headers->set('Cache-Control', 'private, no-store, no-cache, must-revalidate');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->setContentDisposition(
            \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_INLINE,
            basename($fullPath)
        );

        return $response;
    }

    /**
     * Resolve a stored relative path to an absolute on-disk path.
     * Hardened: rejects any path that escapes the screenshots directory.
     */
    private function resolvePath(string $relative): ?string
    {
        $base = realpath(base_path('userfiles') . DIRECTORY_SEPARATOR . 'worktracker' . DIRECTORY_SEPARATOR . 'screenshots');

        if ($base === false) {
            return null;
        }

        $filename = basename($relative);
        $full     = $base . DIRECTORY_SEPARATOR . $filename;

        // Final canonicalisation — block traversal attempts.
        $resolved = realpath($full);
        if ($resolved === false || strpos($resolved, $base) !== 0) {
            return null;
        }

        return $resolved;
    }

    private function guessMime(string $path): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($path) ?: 'application/octet-stream';

        return in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)
            ? $mime
            : 'application/octet-stream';
    }

    /**
     * Privileged lookup (manager+) that bypasses the per-user filter.
     */
    private function findAnyById(int $sessionId): object|false
    {
        $row = app(\Leantime\Core\Db\Db::class)->getConnection()
            ->table('zp_work_sessions')
            ->where('id', $sessionId)
            ->first();

        return $row ?: false;
    }
}
