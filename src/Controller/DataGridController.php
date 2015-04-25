<?php namespace Wms\Admin\DataGrid\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Wms\Admin\DataGrid\Service\TableBuilderService;
use Zend\View\Model\ViewModel;

class DataGridController extends AbstractActionController {

    /**
     * @var TableBuilderService
     */
    protected $tableBuilderService;

    public function __construct(TableBuilderService $tableBuilderService) {
        $this->setTableBuilderService($tableBuilderService);
    }

    public function indexAction() {
        if($this->params()->fromQuery('columns')) {
            $this->getTableBuilderService()->selectColumns($this->params()->fromQuery('columns'));
        }

        if($this->params()->fromQuery('page')) {
            $this->getTableBuilderService()->setPage($this->params()->fromQuery('page'));
        }

        return new ViewModel(array('tableData' => $this->getTableBuilderService()->getTable()));
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