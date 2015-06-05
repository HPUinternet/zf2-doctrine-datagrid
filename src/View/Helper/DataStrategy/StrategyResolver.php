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
     * @return DataStrategyInterface
     */
    public function resolve($data, $propertyName = null)
    {
        $strategy = null;

        // 1. Resolving based on configured values
        if (!is_null($propertyName) && ($configuredStrategy = $this->getConfiguredStrategy($propertyName))) {
            return $this->di->get($configuredStrategy);
        }

        // 2. Resolving based using if statements
        $strategy = $this->quickResolve($data, $propertyName);
        if ($strategy) {
            return $this->di->get($strategy);
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
                return __NAMESPACE__ . '\BooleanStrategy';
                break;
            case is_array($data) == true:
                return __NAMESPACE__ . '\ArrayStrategy';
                break;
            case is_integer($data):
                return __NAMESPACE__ . '\IntegerStrategy';
                break;
            case $data instanceof DateTime:
                return __NAMESPACE__ . '\DateTimeStrategy';
                break;
            case $data instanceof Proxy:
                return __NAMESPACE__ . '\DoctrineProxyStrategy';
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
        if (array_key_exists($propertyName, $this->configuredStrategies)) {
            $strategy = $this->configuredStrategies[$propertyName];

            return strpos($strategy,
                '\\') !== false ? $strategy : __NAMESPACE__ . '\\' . ucfirst($strategy) . 'Strategy';
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
        try {
            $strategy = $this->di->get(
                __NAMESPACE__ . '\\' . ucfirst($dataType) . 'Strategy'
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
