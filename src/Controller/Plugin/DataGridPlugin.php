<?php namespace Wms\Admin\DataGrid\Controller\Plugin;

use Wms\Admin\DataGrid\Service\Interfaces\TableBuilderInterface;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Wms\Admin\DataGrid\Service\TableBuilderService;

class DataGridPlugin extends AbstractPlugin
{
    /**
     * @var TableBuilderService
     */
    protected $tableBuilderService;

    /**
     * @return TableBuilderService
     * @throws \Exception
     */
    public function getTableBuilderService()
    {
        if ($this->tableBuilderService instanceof TableBuilderInterface == false) {
            throw new \Exception("no TableBuilder configured in the DataGrid Controller Plugin.");
        }

        return $this->tableBuilderService;
    }

    /**
     * @param TableBuilderService $tableBuilderService
     */
    public function setTableBuilderService($tableBuilderService)
    {
        $this->tableBuilderService = $tableBuilderService;
    }

    /**
     * Configures the TableBuilderService by checking for several query parameters
     *
     * @param array $queryParameters
     */
    public function processQueryParameters(array $queryParameters)
    {
        if ($columns = $this->getParameter($queryParameters, 'columns')) {
            $this->getTableBuilderService()->selectColumns($this->isJson($columns) ? json_decode($columns) : $columns);
        }

        if (($page = $this->getParameter($queryParameters, 'page'))) {
            $this->getTableBuilderService()->setPage($this->isJson($page) ? json_decode($page) : $page);
        }

        if (
            ($sort = $this->getParameter($queryParameters, 'sort')) &&
            ($order = $this->getParameter($queryParameters, 'order'))
        ) {
            $this->getTableBuilderService()->orderBy(
                $this->isJson($sort) ? json_decode($sort) : $sort,
                $this->isJson($order) ? json_decode($order) : $order
            );
        }

        if ($search = $this->getParameter($queryParameters, 'search')) {
            $this->getTableBuilderService()->search($this->isJson($search) ? json_decode($search) : $search);
        }
    }

    /**
     * One fits all solution callable method, returns your DataGrid tableData.
     *
     * @param array $queryParameters
     * @return mixed
     */
    public function getTable(array $queryParameters = array())
    {
        if (!empty($queryParameters)) {
            $this->processQueryParameters($queryParameters);
        }

        return $this->tableBuilderService->getTable();
    }

    /**
     * wrapper around a if statement. will return the value in the array if available
     * else returns false
     *
     * @param array $queryParameters
     * @param $parameterName
     * @return mixed
     */
    protected function getParameter(array $queryParameters, $parameterName)
    {
        if (isset($queryParameters[$parameterName]) && !empty($queryParameters[$parameterName])) {
            return $queryParameters[$parameterName];
        }

        return false;
    }

    /**
     * Checks if a string can be converted to json
     *
     * @param $string
     * @return bool
     */
    protected function isJson($string)
    {
        if (is_array($string) || is_numeric($string)) {
            return false;
        }

        json_decode($string);

        return (json_last_error() == JSON_ERROR_NONE);
    }
}