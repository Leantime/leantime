<?php

namespace Leantime\Domain\Files\Controllers;

use Illuminate\Http\Request;
use Leantime\Domain\Files\Services\Files as FileService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles multipart file uploads (editor image paste/drop, Uppy file manager).
 *
 * A native Laravel controller (constructor DI, route-bound action). Relocated from the
 * retired Api\Controllers\Files. Bound in Files/routes.php at the canonical /files/upload
 * plus the backward-compatible /api/files alias used by Tiptap and Uppy. The dead
 * paste-fallback branch, the unrelated PATCH (user-settings) handler and the 501 stubs
 * from the old controller are intentionally not carried over.
 *
 * The success response MUST stay the raw upload() metadata array — Tiptap reads
 * data.module/encName/extension/realName and Uppy reads the same off response.body.
 */
class Upload
{
    public function __construct(private FileService $fileService) {}

    /**
     * POST — store an uploaded file against a module/moduleId (both from the query string;
     * the file is the multipart field "file"). Returns the upload() metadata array as JSON.
     */
    public function post(Request $request): Response
    {
        $module = $request->query('module');
        $moduleId = $request->query('moduleId');

        // Missing required parts is a client error, not a server fault.
        if (! isset($_FILES['file']) || $module === null || $moduleId === null) {
            return response()->json(['status' => 'error', 'message' => 'Missing file, module or moduleId'], 400);
        }

        $module = htmlentities($module);
        $id = (int) $moduleId;

        // The legacy endpoint had no project gate: a logged-in user could attach files to
        // any module/moduleId by tampering with the query string. Authorize against the
        // target's project (admins/owners bypass; modules with no project mapping fall back
        // to the read-path behaviour in Files::getFileForUser()).
        if (! $this->fileService->userCanUploadToModule($module, $id)) {
            return response()->json(['status' => 'unauthorized'], 403);
        }

        $result = $this->fileService->upload($_FILES, $module, $id);

        if (is_string($result)) {
            return response()->json(['status' => 'error', 'message' => $result], 500);
        }

        return response()->json($result);
    }
}
