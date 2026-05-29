<?php

namespace Leantime\Domain\Files\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Files\Services\Files as FileService;
use Symfony\Component\HttpFoundation\Response;

class Get extends Controller
{
    private FileService $filesService;

    /**
     * Initializes the controller with required dependencies.
     *
     * @param  FileService  $filesService  The file service for retrieval and authorization.
     */
    public function init(FileService $filesService): void
    {
        $this->filesService = $filesService;
    }

    /**
     * Handles GET requests to download/view a file.
     *
     * Validates that the current user has access to the project the file belongs to
     * before serving the file content.
     *
     * @return Response The file content response, 403 if unauthorized, or 404 if not found.
     *
     * @throws \Exception
     */
    public function get(): Response
    {
        $rawEncName = $_GET['encName'] ?? '';
        $encName = preg_replace('/[^a-zA-Z0-9]+/', '', $rawEncName);

        if (empty($encName)) {
            return new Response('Bad request', 400);
        }

        return $this->filesService->getFileForUser($encName, (int) session('userdata.id'));
    }
}
