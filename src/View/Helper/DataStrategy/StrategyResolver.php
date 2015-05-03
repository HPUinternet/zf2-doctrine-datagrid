<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

use DateTime;
use Wms\Admin\DataGrid\View\Helper\DataStrategy\DataStrategyInterface;
use Zend\Di\Di;
use Doctrine\ORM\Proxy\Proxy;

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

    public function __construct()
    {
        $this->setDi(new Di());
        $this->addDependency($this, __CLASS__);
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

        // 3. Resolving using an event, calling the ones who can parse and taking the first one
        $strategy = $this->eventResolve($data, $propertyName);
        if ($strategy) {
            return $this->di->get($strategy);
        }

        // 4. Resolving using the string strategy:  no-one beats a plain old echo statement
        return $this->di->get('Wms\Admin\DataGrid\View\Helper\DataStrategy\StringStrategy');
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
     * Resolve a strategy by triggering an event in the application, calling all registered parsing strategies
     *
     * @param $data
     * @param $propertyName
     * @return bool
     */
    public function eventResolve($data, $propertyName)
    {
        /* @todo: implement me, find the strategy by calling all listeners for a method called canParse($value)
         * The strategies shall return true if they are able to parse the data, the resolver will then use this strategy
         */
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