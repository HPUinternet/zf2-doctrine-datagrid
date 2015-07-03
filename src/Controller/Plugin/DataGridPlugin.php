<?php namespace Wms\Admin\DataGrid\Controller\Plugin;

use Wms\Admin\DataGrid\Service\Interfaces\TableBuilderInterface;
use Zend\Json\Exception\RuntimeException;
use Zend\Json\Json;
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
        if (isset($queryParameters['columns']) && !empty($queryParameters['columns'])) {
            $columns = $this->parseValue($queryParameters['columns']);
            $this->getTableBuilderService()->selectColumns($columns);
        }

        if (isset($queryParameters['page'])&& !empty($queryParameters['page'])) {
            $page = $this->parseValue($queryParameters['page']);
            $this->getTableBuilderService()->setPage($page);
        }

        if (isset($queryParameters['sort']) && isset($queryParameters['order']) && !empty($queryParameters['sort'])) {
            $sort = $this->parseValue($queryParameters['sort']);
            $order = $this->parseValue($queryParameters['order']);

            $this->getTableBuilderService()->orderBy($sort, $order);
        }

        if (isset($queryParameters['search']) && !empty($queryParameters['search'])) {
            $search = $this->parseValue($queryParameters['search']);
            $this->getTableBuilderService()->search($search);
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
     * @param $value
     * @return mixed
     */
    protected function parseValue($value)
    {
        if (!is_array($value)) {
            try {
                return Json::decode($value);
            } catch (RuntimeException $ex) {
            }
        }

        return $value;
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

        Json::decode($string);

        return (json_last_error() == JSON_ERROR_NONE);
    }
}
