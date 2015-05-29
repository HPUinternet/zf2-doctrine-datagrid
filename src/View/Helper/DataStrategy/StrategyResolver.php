<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

use DateTime;
use Zend\Di\Di;
use Doctrine\ORM\Proxy\Proxy;
use Zend\Di\Exception\ClassNotFoundException;

class StrategyResolver
{

    /**
     * @var View
     */
    private $view;

    /**
     * @var Di;
     */
    protected $di;

    /**
     * @var object
     */
    public $defaultStrategy;

    public function __construct()
    {
        $this->setDi(new Di());
        $this->addDependency($this, __CLASS__);
        $this->defaultStrategy = $this->di->get('Wms\Admin\DataGrid\View\Helper\DataStrategy\StringStrategy');
    }

    /**
     * @param $data
     * @return DataStrategyInterface
     */
    public function resolve($data, $propertyName = null)
    {
        $strategy = null;

        // 1. Resolving based on configured values
        if (!is_null($propertyName) && ($configuredStrategy = $this->getConfiguredStrategy($propertyName))) {
            return $configuredStrategy;
        }

        // 2. Resolving based using if statements
        $strategy = $this->quickResolve($data, $propertyName);
        if ($strategy) {
            return $this->di->get($strategy);
        }

        // 3. Resolving using the default strategy;
        return $this->defaultStrategy;
    }

    public function quickResolve($data, $propertyName)
    {
        $strategyPrefix = 'Wms\Admin\DataGrid\View\Helper\DataStrategy\\';
        switch (true) {
            case is_bool($data) || (($data === 1 || $data === 0) && strpos($propertyName, 'id') === false):
                return $strategyPrefix . 'BooleanStrategy';
                break;
            case is_array($data) == true:
                return $strategyPrefix . 'ArrayStrategy';
                break;
            case is_integer($data):
                return $strategyPrefix . 'IntegerStrategy';
                break;
            case $data instanceof DateTime:
                return $strategyPrefix . 'DateTimeStrategy';
                break;
            case $data instanceof Proxy:
                return $strategyPrefix . 'DoctrineProxyStrategy';
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * Resolve a strategy by checking if someone configured a strategy for the property
     *
     * @param $propertyName
     * @return bool
     */
    public function getConfiguredStrategy($propertyName)
    {
        // @todo: implement me, find the strategy using array_key_exists
        return false;
    }

    /**
     * Resolve and parse the value
     *
     * @param $data
     * @param null $propertyName
     * @return mixed
     */
    public function resolveAndParse($data, $propertyName = null)
    {
        $strategy = $this->resolve($data, $propertyName);

        return $strategy->parse($data);
    }

    /**
     * Find and resolve the appropriate filter strategy for a dataType
     *
     * @param $elementName
     * @param $dataType
     * @return string
     */
    public function displayFilterForDataType($elementName, $dataType)
    {
        try {
            $strategy = $this->di->get(
                'Wms\Admin\DataGrid\View\Helper\DataStrategy\\' . ucfirst($dataType) . 'Strategy'
            );
            if ($strategy instanceof DataStrategyFilterInterface) {
                return $strategy->showFilter($elementName);
            }
        } catch (ClassNotFoundException $ex) {
            return $this->defaultStrategy->showFilter($elementName);
        }
        return $this->defaultStrategy->showFilter($elementName);
    }

    /**
     * Add a new object to the DI container
     *
     * @param $instance
     * @param $classOrAlias
     * @return $this
     */
    public function addDependency($instance, $classOrAlias)
    {
        $this->getDi()->instanceManager()->addSharedInstance($instance, $classOrAlias);

        return $this;
    }

    /**
     * @return Di
     */
    public function getDi()
    {
        return $this->di;
    }

    /**
     * @param Di $di
     */
    public function setDi(Di $di)
    {
        $this->di = $di;
    }
}
