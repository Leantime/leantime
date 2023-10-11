<?php

namespace Leantime\Domain\CsvImport\Controllers;

use Leantime\Domain\CsvImport\Services\CsvImport as CsvImportService;
use Illuminate\Contracts\Container\BindingResolutionException;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\Statement;
use Leantime\Core\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\CsvImport\Controllers\models;
use Leantime\Domain\CsvImport\Controllers\services;

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
     * @return void
     */
    public function init(CsvImportService $providerService): void
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        $this->providerService = $providerService;
    }

    /**
     * get - display upload form
     *
     * @access public
     * @return void
     * @throws \Exception
     * @throws \Exception
     */
    public function get(): void
    {
        $this->tpl->displayPartial("csvImport.upload");
    }

    /**
     * post - process uploaded file
     *
     * @access public
     * @param array $params
     * @return void
     * @throws BindingResolutionException
     * @throws Exception
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

        $integration = new models\connector\integration();
        $integration->fields = implode(",", $header);

        //Temporarily store results in meta

        $_SESSION['csv_records'] = iterator_to_array($records);

        $integrationService = new services\connector\integrations();
        $id = $integrationService->create($integration);

        $this->tpl->displayJson(json_encode(array("id"=>$id)));

    }
}
