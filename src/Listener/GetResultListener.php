<?php

namespace Wms\Admin\DataGrid\Listener;

use Wms\Admin\DataGrid\Event\DataGridEvents;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\ModuleManager\Listener\AbstractListener;

/**
 * add this to the module.config where you want to use attach the listener
 *  'service_manager' => array(
 *      'invokables' => array(
 *          'DataGridGetResultListener' => 'NameSpace\DataGridGetResultListener',
 *      ),
 *  ),
 *  'listeners' => array(
 *      'DataGridGetResultListener',
 *  ),
 *
 * Class GetResultListener
 */
class GetResultListener extends AbstractListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     * Attach one or more listeners
     *
     * @param  EventManagerInterface $events
     * @return GetResultListener
     */
    public function attach(EventManagerInterface $events)
    {
        $events = $events->getSharedManager();
        $this->listeners[] = $events->attach(
            'Wms\Admin\DataGrid\Service\QueryBuilderService',
            DataGridEvents::DATAGRID_PRE_GETRESULTSET,
            array($this, 'getResultSet'),
            100
        );
        return $this;
    }

    /**
     * @param EventInterface $eventArgs
     * @throws \Exception
     */
    public function getResultSet(EventInterface $eventArgs)
    {
        throw new \Exception("listener should have a own implementation of getResultSet");
    }
}
