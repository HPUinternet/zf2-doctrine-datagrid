<?php namespace Wms\Admin\DataGrid\View\Helper\DataGrid;

use Wms\Admin\DataGrid\Event\DataGridEvents;
use Wms\Admin\DataGrid\Model\TableFilterModel;
use Wms\Admin\DataGrid\Model\TableHeaderCellModel;
use Zend\Di\ServiceLocator;
use Zend\Form\Element;

abstract class TableHeader
{
    /**
     * returns the opening table element
     * @param $displaySettings
     * @param string $classes
     * @return string
     */
    public static function printTableStart(
        $displaySettings,
        $classes = "table tabelVerkenner table-striped table-hover table-condensed"
    ) {
        $html = '';
        if (in_array('simpleSearch', $displaySettings)) {
            $html .= '<form method="GET">';
        }
        $html .= sprintf('<table class="%s">', $classes);

        return $html;
    }

    /**
     * returns the table heading, containing the named table columns
     *
     * @param Table $tableHelper
     * @param string $classes
     * @return string
     */
    public static function printTableHeadRow(Table $tableHelper, $classes = "tabelHeader")
    {
        $hasOrdering = in_array('ordering', $tableHelper->getDisplaySettings());
        $html = '<thead>';
        $html .= '<tr>';

        self::getHeaderColumnsFromListeners($tableHelper);

        /** @var TableHeaderCellModel $tableHeader */
        foreach ($tableHelper->getDisplayedHeaders() as $key => $tableHeader) {
            $html .= sprintf(
                '<th class="%s" ',
                $classes . " " . $tableHeader->getSafeName()
            );

            if ($tableHeader->getWidth() > 0) {
                $html .= 'style="width: ' . $tableHeader->getWidth() . 'px;"';
            }

            $html .= '>';
            $html .= $tableHelper->getView()->translate($tableHeader->getName());

            if ($tableHeader->getName() == $tableHeader->getAccessor() && $hasOrdering && $tableHeader->isOrderable()) {
                $html .= self::printOrderOption($tableHelper, $tableHeader->getName());
            }

            $html .= '</th>';
        }

        /** @var TableFilterModel $filter */
        foreach ($tableHelper->getAdditionalFilters() as $filter) {
            $html .= sprintf(
                '<th class="%s customFilter">%s</th>',
                $classes . " " . $filter->getSafeName(),
                $filter->getName()
            );
        }

        $addSearchColumn = (
            in_array('simpleSearch', $tableHelper->getDisplaySettings()) ||
            in_array('actionRoutes', $tableHelper->getDisplaySettings())
        );

        if ($addSearchColumn) {
            $html .= '<th class="tabelHeader rowOptions"></th>';
        }

        $html .= '</tr>';
        $html .= TableSearchFilter::printSearchFilter($tableHelper);
        $html .= '</thead>';

        return $html;
    }


    /**
     * if configured, Prints the "order by" icons in each table heading cell
     * @param Table $tableHelper
     * @param $columnName
     * @return string|void
     */
    public static function printOrderOption(Table $tableHelper, $columnName)
    {
        $downUrl = $tableHelper->getView()->UrlWithQuery(array('sort' => $columnName, 'order' => 'desc'));
        $upUrl = $tableHelper->getView()->UrlWithQuery(array('sort' => $columnName, 'order' => 'asc'));
        $iconClass = 'glyphicon glyphicon-chevron';
        $html = '<span class="pull-right">
                    <a href="' . $downUrl . '" class="tabelHeadOpties"><i class="' . $iconClass . '-down"></i></a>
                    <a href="' . $upUrl . '" class="tabelHeadOpties"><i class="' . $iconClass . '-up"></i></a>
                 </span>';

        return $html;
    }

    /**
     * @param Table $tableHelper
     * @return mixed
     */
    public static function getHeaderColumnsFromListeners(Table $tableHelper)
    {
        $responseCollection = $tableHelper->getEventManager()->trigger(
            DataGridEvents::DATAGRID_PRE_PRINTTABLEHEADROW,
            __CLASS__,
            array('tableHelper' => $tableHelper)
        );
        foreach ($responseCollection as $response) {
            if (is_array($response) && isset($response['tableHelper'])) {
                $tableHelper = $response['tableHelper'];
            }
        }
        return $tableHelper;
    }
}
