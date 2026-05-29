<?php

namespace Leantime\Domain\CsvImport\Controllers;

use League\Csv\Exception;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\CsvImport\Services\CsvImport as CsvImportService;
use Symfony\Component\HttpFoundation\Response;

/**
 * upload controller for csvImport plugin
 */
class Upload extends Controller
{
    private CsvImportService $providerService;

    /**
     * constructor - initialize private variables
     */
    public function init(CsvImportService $providerService): void
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        $this->providerService = $providerService;
    }

    /**
     * get - display upload form
     *
     * @throws \Exception
     * @throws \Exception
     */
    public function get(): Response
    {
        return $this->tpl->displayPartial('csvImport.upload');
    }

    /**
     * post - process uploaded file
     */
    public function post(array $params): Response
    {
        $file = $this->incomingRequest->file('file');

        try {
            $id = $this->providerService->processUpload($file);
        } catch (Exception $e) {
            return $this->tpl->displayJson(json_encode(['error' => $e->getMessage()]), 500);
        }

        return $this->tpl->displayJson(json_encode(['id' => $id]));
    }
}
