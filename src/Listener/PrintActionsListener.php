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
 *          'DataGridPrintActionsListener' => 'NameSpace\DataGridPrintActionsListener',
 *      ),
 *  ),
 *  'listeners' => array(
 *      'DataGridPrintActionsListener',
 *  ),
 *
 * Class PrintActionsListener
 */
class PrintActionsListener extends AbstractListener implements ListenerAggregateInterface
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
            'Wms\Admin\DataGrid\View\Helper\DataGrid\Table',
            DataGridEvents::DATAGRID_PRE_PRINTTABLECONTENTROWACTIONS,
            array($this, 'printActions'),
            100
        );
        return $this;
    }

    /**
     * @param EventInterface $eventArgs
     * @throws \Exception
     */
    public function printActions(EventInterface $eventArgs)
    {
        throw new \Exception("listener should have a own implementation of printActions");
    }
}
