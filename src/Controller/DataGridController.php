<?php namespace Wms\Admin\DataGrid\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Wms\Admin\DataGrid\Service\TableBuilderService;
use Zend\View\Model\ViewModel;

class DataGridController extends AbstractActionController
{

    /**
     * @var TableBuilderService
     */
    protected $tableBuilderService;

    public function __construct(TableBuilderService $tableBuilderService)
    {
        $this->tableBuilderService = $tableBuilderService;
    }

    public function indexAction()
    {
        if($this->params()->fromQuery('columns')) {
            $this->tableBuilderService->selectColumns($this->params()->fromQuery('columns'));
        }

        if($this->params()->fromQuery('page')) {
            $this->tableBuilderService->setPage($this->params()->fromQuery('page'));
        }

        if($this->params()->fromQuery('sort') && $this->params()->fromQuery('order')) {
            $this->tableBuilderService->orderBy(
                $this->params()->fromQuery('sort'),
                $this->params()->fromQuery('order')
            );
        }

        return new ViewModel(array('tableData' => $this->tableBuilderService->getTable()));
    }

    public function deleteSelectedEntities() {

    }
}