<?php

namespace Leantime\Domain\CsvImport\Controllers;

use Illuminate\Support\Facades\Log;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Connector\Permissions\ConnectorPermissions;
use Leantime\Domain\CsvImport\Exceptions\ImportFileException;
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
        $this->providerService = $providerService;
    }

    /**
     * get - display upload form
     *
     * @throws \Exception
     * @throws \Exception
     */
    #[RequiresPermission(ConnectorPermissions::MANAGE, global: true)]
    public function get(): Response
    {
        return $this->tpl->displayPartial('csvImport.upload');
    }

    /**
     * post - process uploaded file
     */
    #[RequiresPermission(ConnectorPermissions::MANAGE, global: true)]
    public function post(array $params): Response
    {
        $file = $this->incomingRequest->file('file');
        if ($file === null) {
            return $this->tpl->displayJson(json_encode(['error' => $this->language->__('notification.import_file_unreadable')]), 422);
        }

        try {
            $id = $this->providerService->processUpload($file);
        } catch (ImportFileException $e) {
            // Written for the person: what's wrong with the file and what to do.
            return $this->tpl->displayJson(json_encode(['error' => $e->getMessage()]), 422);
        } catch (\Throwable $e) {
            Log::error($e);

            return $this->tpl->displayJson(json_encode(['error' => $this->language->__('notification.import_file_unreadable')]), 500);
        }

        return $this->tpl->displayJson(json_encode(['id' => $id]));
    }
}
