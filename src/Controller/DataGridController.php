<?php namespace Wms\Admin\DataGrid\Controller;

use Zend\EventManager\EventManager;
use Zend\Mvc\Controller\AbstractActionController;
use Wms\Admin\DataGrid\Service\TableBuilderService;
use Zend\View\Model\ViewModel;

class DataGridController extends AbstractActionController
{
    /**
     * @var TableBuilderService
     */
    protected $tableBuilderService;

    /**
     * @param TableBuilderService $tableBuilderService
     */
    public function __construct(TableBuilderService $tableBuilderService)
    {
        $this->tableBuilderService = $tableBuilderService;
    }

    /**
     * The index page
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        if ($this->params()->fromQuery('columns')) {
            $columns = $this->params()->fromQuery('columns');
            $this->tableBuilderService->selectColumns($this->isJson($columns) ? json_decode($columns) : $columns);
        }

        if ($this->params()->fromQuery('page')) {
            $page = $this->params()->fromQuery('page');
            $this->tableBuilderService->setPage($this->isJson($page) ? json_decode($page) : $page);
        }

        if ($this->params()->fromQuery('sort') && $this->params()->fromQuery('order')) {
            $sort = $this->params()->fromQuery('sort');
            $order = $this->params()->fromQuery('order');
            $this->tableBuilderService->orderBy(
                $this->isJson($sort) ? json_decode($sort) : $sort,
                $this->isJson($order) ? json_decode($order) : $order
            );
        }

        if ($this->params()->fromQuery('search')) {
            $search = $this->params()->fromQuery('search');
            $this->tableBuilderService->search($this->isJson($search) ? json_decode($search) : $search);
        }

        return new ViewModel(array('tableData' => $this->tableBuilderService->getTable()));
    }

    /**
     * Checks if a string can be converted to json
     *
     * @param $string
     * @return bool
     */
    private function isJson($string)
    {
        if (is_array($string) || is_numeric($string)) {
            return false;
        }

        json_decode($string);

        return (json_last_error() == JSON_ERROR_NONE);
    }
}
