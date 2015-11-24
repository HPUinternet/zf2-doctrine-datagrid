<?php

namespace Wms\Admin\DataGrid\Event;

use Zend\EventManager\Event;

/**
 * This class serves as a event container for the events triggered by the DataGrid
 *
 * Class DataGridEvents
 */
class DataGridEvents extends Event
{
    /**
     * events triggered by DataGrid
     */
    // QueryBuilderService events
    const DATAGRID_PRE_GETRESULTSET = 'dataGridPreGetResultSet';

    // Table ViewHelper events
    const DATAGRID_PRE_PRINTTABLECONTENTROWACTIONS = 'dataGridPrePrintTableContentRowActions';
    const DATAGRID_PRE_PRINTTABLEHEADROW = 'dataGridPrePrintTableHeadRow';
}