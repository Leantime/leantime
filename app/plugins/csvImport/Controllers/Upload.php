<?php

namespace Leantime\Plugins\CsvImport\Controllers;

use League\Csv\Statement;
use Leantime\Core\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Connector\Services\Integrations as IntegrationService;
use Leantime\Domain\Connector\Models\Integration as IntegrationModel;
use Leantime\Domain\Auth\Services\Auth;
use League\Csv\Reader;
use Leantime\Plugins\CsvImport\Services\CsvImport as CsvImportService;

/**
 * upload controller for csvImport plugin
 */
class Upload extends Controller
{
    /**
     * @var CsvImportService
     */
    private CsvImportService $providerService;

    /**
     * constructor - initialize private variables
     *
     * @access public
     * @param  CsvImportService $providerService
     * @return self
     */
    public function init(CsvImportService $providerService)
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        $this->providerService = $providerService;
    }

    /**
     * get - display upload form
     *
     * @access public
     * @return void
     */
    public function get()
    {
        $this->tpl->displayPartial("csvImport.upload");
    }

    /**
     * post - process uploaded file
     *
     * @access public
     * @param  array $params
     * @return void
     */
    public function post(array $params): void
    {
        $csv = Reader::createFromPath($_FILES['file']['tmp_name'], 'r');

        $csv->setHeaderOffset(0);

        $records = Statement::create()->process($csv);

        $header = $records->getHeader();  //returns the CSV header record
        $records = $csv->getRecords(); //returns all the CSV records as an Iterator object

        $rows = array();
        foreach ($records as $offset => $record) {
            $rows[] = $record;
        }

        $integration = app()->make(IntegrationModel::class);
        $integration->fields = implode(",", $header);

        //Temporarily store results in meta
        $integration->meta = serialize($rows);

        $integrationService = app()->make(IntegrationService::class);
        $id = $integrationService->create($integration);

        $this->tpl->displayJson(json_encode(array("id" => $id)));
    }
}
