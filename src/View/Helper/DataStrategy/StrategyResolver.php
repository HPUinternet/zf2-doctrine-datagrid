<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

use DateTime;
use Zend\Di\Di;
use Doctrine\ORM\Proxy\Proxy;
use Zend\Di\Exception\ClassNotFoundException;
use Zend\Di\Exception\RuntimeException;

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

    /**
     * @var Array
     */
    private $configuredStrategies;

    /**
     * Configures the strategy resolver
     *
     * You can pass an optional array of fieldNames and renderStrategies in the constructor of this class
     * to ensure your field from a specific rendering strategy. This array can look like the example below:
     * array(
     *      'fieldName' => 'string'
     *      'category.name' => '\MyApplication\DataStrategy\CustomCategoryNameStrategy'
     * );
     *
     * @param array $configuredStrategies
     */
    public function __construct($configuredStrategies = array())
    {
        $this->setDi(new Di());
        $this->addDependency($this, __CLASS__);
        $this->configuredStrategies = $configuredStrategies;
        $this->defaultStrategy = $this->di->get(__NAMESPACE__ . '\StringStrategy');
    }

    /**
     * @param $data
     * @param null $propertyName
     * @return DataStrategyInterface
     */
    public function resolve($data, $propertyName = null)
    {
        $strategy = false;

        // 1. Resolving based on configured values
        if (!is_null($propertyName) && ($strategy = $this->getConfiguredStrategy($data, $propertyName))) {
            return $strategy;
        }

        // 2. Resolving based using if statements
        if (($strategy = $this->quickResolve($data, $propertyName))) {
            return $strategy;
        }

        // 3. Resolving using the default strategy;
        return $this->defaultStrategy;
    }

    /**
     * Quick resolve a datatype by invoking a case - switch statement
     *
     * @param $data
     * @param $propertyName
     * @return bool|string
     */
    public function quickResolve($data, $propertyName)
    {
        switch (true) {
            case is_bool($data) || (($data === 1 || $data === 0) && strpos($propertyName, 'id') === false):
                $strategyName = $this->getStrategyNamespace('Boolean');
                break;
            case is_array($data) == true:
                $strategyName = $this->getStrategyNamespace('Array');
                break;
            case is_integer($data):
                $strategyName = $this->getStrategyNamespace('Integer');
                break;
            case $data instanceof DateTime:
                $strategyName = $this->getStrategyNamespace('Datetime');
                break;
            case $data instanceof Proxy:
                $strategyName = $this->getStrategyNamespace('DoctrineProxy');
                break;
            default:
                return false;
                break;
        }

        return $this->di->get($strategyName);
    }

    /**
     * Resolve a strategy by checking if someone configured a strategy for the property
     *
     * @param $propertyName
     * @return bool
     */
    public function getConfiguredStrategy($data, $propertyName)
    {
        if (!is_array($data) && array_key_exists($propertyName, $this->configuredStrategies)) {
            $strategy = $this->configuredStrategies[$propertyName];

            return $this->di->get($this->getStrategyNamespace($strategy));
        }

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
        $strategy = $this->di->get($this->getStrategyNamespace($dataType));
        if ($strategy instanceof DataStrategyFilterInterface) {
            return $strategy->showFilter($elementName);
        }

        return $this->defaultStrategy->showFilter($elementName);
    }

    /**
     * This function Identifies internal or external strategies and converts namespaces accordingly
     *
     * @param $strategyName
     * @return string
     */
    private function getStrategyNamespace($strategyName)
    {
        $isExternal = (strpos($strategyName, '\\') !== false);
        if ($isExternal) {
            return $strategyName;
        }

        preg_match('#^\p{Lu}#u', $strategyName, $matches);
        if (count($matches) <= 0) {
            $strategyName = ucfirst($strategyName);
        }

        return __NAMESPACE__ . '\\' . ucfirst($strategyName) . 'Strategy';
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
