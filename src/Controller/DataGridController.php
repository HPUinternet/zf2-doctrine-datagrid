<?php namespace Wms\Admin\DataGrid\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Wms\Admin\DataGrid\Service\TableBuilderService;

class DataGridController extends AbstractActionController {

    /**
     * @var TableBuilderService
     */
    protected $tableBuilderService;

    public function __construct(TableBuilderService $tableBuilderService) {
        $this->setTableBuilderService($tableBuilderService);
    }

    public function indexAction() {
        $table = $this->getTableBuilderService()->getTable();
        die('eind debug van de indexaction in de controller');
    }

    /**
     * @return TableBuilderService
     */
    public function getTableBuilderService() {
        return $this->tableBuilderService;
    }

    /**
     * @param TableBuilderService $tableBuilderService
     */
    public function setTableBuilderService($tableBuilderService) {
        $this->tableBuilderService = $tableBuilderService;
    }
}