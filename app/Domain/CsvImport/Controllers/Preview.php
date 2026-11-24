<?php

namespace Leantime\Domain\CsvImport\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Connector\Permissions\ConnectorPermissions;
use Leantime\Domain\CsvImport\Exceptions\ImportFileException;
use Leantime\Domain\CsvImport\Services\CsvImport as CsvImportService;
use Leantime\Domain\Plugins\Services\Plugins;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shows what was found in an uploaded file before anything is mapped: format, sheet, rows,
 * heading row, columns and the first rows. The person can pick another sheet or heading row.
 */
class Preview extends Controller
{
    /** Rows shown in the preview. */
    private const PREVIEW_ROWS = 5;

    /** Above this many rows the page says the import may take a while. */
    public const LARGE_FILE_ROWS = 1000;

    /** The plugin that lets an AI assistant do a more complicated move. */
    public const MCP_PLUGIN = 'McpServer';

    private CsvImportService $csvImport;

    private Plugins $plugins;

    /**
     * Initializes the controller dependencies.
     */
    public function init(CsvImportService $csvImport, Plugins $plugins): void
    {
        $this->csvImport = $csvImport;
        $this->plugins = $plugins;
    }

    /**
     * Shows the file summary and preview.
     *
     * @param  array  $params  Request parameters; integrationId is the upload's integration record.
     */
    #[RequiresPermission(ConnectorPermissions::MANAGE, global: true)]
    public function get(array $params): Response
    {
        $table = $this->csvImport->currentTable();
        $integrationId = (int) ($params['integrationId'] ?? 0);

        if ($table === null || $integrationId <= 0) {
            $this->tpl->setNotification($this->language->__('notification.import_file_gone'), 'error');

            return Frontcontroller::redirect(BASE_URL.'/connector/integration?provider='.$this->csvImport->id);
        }

        $this->tpl->assign('provider', $this->csvImport);
        $this->tpl->assign('table', $table);
        $this->tpl->assign('preview', $table->preview(self::PREVIEW_ROWS));
        $this->tpl->assign('integrationId', $integrationId);
        $this->tpl->assign('largeFile', $table->rowCount() > self::LARGE_FILE_ROWS);
        $this->tpl->assign('mcpServerEnabled', $this->plugins->isEnabled(self::MCP_PLUGIN));

        return $this->tpl->display('csvImport.preview');
    }

    /**
     * Reads the file again with the sheet or heading row the person picked.
     *
     * @param  array  $params  Request parameters: integrationId, sheetIndex, headerRow.
     */
    #[RequiresPermission(ConnectorPermissions::MANAGE, global: true)]
    public function post(array $params): Response
    {
        $integrationId = (int) ($params['integrationId'] ?? 0);
        $sheetIndex = isset($params['sheetIndex']) && is_numeric($params['sheetIndex']) ? (int) $params['sheetIndex'] : null;
        $headerRow = isset($params['headerRow']) && is_numeric($params['headerRow']) && (int) $params['headerRow'] > 0 ? (int) $params['headerRow'] : null;

        // A different sheet has its own heading row: find it again.
        $current = $this->csvImport->currentTable();
        if ($current !== null && $sheetIndex !== null && $sheetIndex !== $current->sheetIndex) {
            $headerRow = null;
        }

        try {
            $this->csvImport->rereadUpload($integrationId, $sheetIndex, $headerRow);
        } catch (ImportFileException $e) {
            $this->tpl->setNotification($e->getMessage(), 'error');
        }

        return Frontcontroller::redirect(BASE_URL.'/csvImport/preview?integrationId='.$integrationId);
    }
}
