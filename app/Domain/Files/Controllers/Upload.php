<?php

namespace Leantime\Domain\Files\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Files\Services\Files as FileService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles multipart file uploads (editor image paste/drop, Uppy file manager).
 *
 * Relocated from the retired Api\Controllers\Files so the behaviour lives in the
 * Files domain. Reachable at the canonical /files/upload and at the backward-compatible
 * /api/files alias (registered in Files/routes.php) used by Tiptap and Uppy. The dead
 * paste-fallback branch, the unrelated PATCH (user-settings) handler and the 501 stubs
 * from the old controller are intentionally not carried over.
 *
 * The success response MUST stay the raw upload() metadata array — Tiptap reads
 * data.module/encName/extension/realName and Uppy reads the same off response.body.
 */
class Upload extends Controller
{
    private FileService $fileService;

    public function init(FileService $fileService): void
    {
        $this->fileService = $fileService;
    }

    /**
     * POST — store an uploaded file against a module/moduleId.
     *
     * module and moduleId arrive on the query string (?module=&moduleId=); the file
     * is the multipart field "file". Returns the upload() metadata array as JSON.
     */
    public function post(array $params): Response
    {
        if (! isset($_FILES['file'], $_GET['module'], $_GET['moduleId'])) {
            return $this->tpl->displayJson(['status' => 'Something unexpected'], 500);
        }

        $module = htmlentities($_GET['module']);
        $id = (int) $_GET['moduleId'];

        $result = $this->fileService->upload($_FILES, $module, $id);

        if (is_string($result)) {
            return $this->tpl->displayJson(['status' => 'error', 'message' => $result], 500);
        }

        return $this->tpl->displayJson($result);
    }
}
