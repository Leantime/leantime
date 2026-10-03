<?php

namespace Leantime\Domain\Status\Controllers;

use Illuminate\Database\DatabaseManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

/**
 * Public, unauthenticated liveness/readiness probe — GET /health.
 *
 * Meant for Docker/Kubernetes health checks and uptime monitors. Returns 200 {"status":"ok"}
 * when the app boots and the database answers a trivial query, 503 {"status":"error"} otherwise.
 *
 * SECURITY — unauthenticated, so the body never contains a version, hostnames, error messages
 * or any other detail. HttpKernel dispatches this route without the middleware pipeline (no
 * session, install/update redirects, auth or rate limiting) so probes stay cheap and an
 * in-progress db update doesn't turn into a 302 that orchestrators read as "unhealthy".
 */
class Health
{
    public function __construct(private DatabaseManager $db) {}

    /**
     * Run the health check and return the probe response.
     */
    public function get(): JsonResponse
    {
        $headers = ['Cache-Control' => 'no-store'];

        if (! $this->databaseIsReachable()) {
            return new JsonResponse(['status' => 'error'], JsonResponse::HTTP_SERVICE_UNAVAILABLE, $headers);
        }

        return new JsonResponse(['status' => 'ok'], JsonResponse::HTTP_OK, $headers);
    }

    /**
     * Cheap round trip to the default connection. Any failure (connection refused, auth, timeout)
     * counts as unreachable; details are deliberately not logged since probes run every few seconds.
     */
    private function databaseIsReachable(): bool
    {
        try {
            $this->db->connection()->select('select 1');

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
