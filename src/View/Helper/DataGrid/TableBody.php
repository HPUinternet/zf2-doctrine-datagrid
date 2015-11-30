<?php namespace Wms\Admin\DataGrid\View\Helper\DataGrid;

use Wms\Admin\DataGrid\Event\DataGridEvents;
use Wms\Admin\DataGrid\Model\TableCellModel;
use Wms\Admin\DataGrid\Model\TableHeaderCellModel;
use Wms\Admin\DataGrid\Model\TableModel;
use Wms\Admin\DataGrid\Model\TableRowModel;
use Wms\Admin\DataGrid\Model\TableFilterModel;
use Wms\Admin\DataGrid\View\Helper\DataStrategy\StrategyResolver;
use Zend\Di\ServiceLocator;
use Zend\Form\Element;

abstract class TableBody
{
    /**
     * Looper for the table content
     *
     * @param Table $tableHelper
     * @param string $trClass
     * @return string
     */
    public static function printTableBody(Table $tableHelper, $trClass = "")
    {
        $html = '';
        $rows = $tableHelper->getTableModel()->getTableRows();

        if (empty($rows)) {
            $html .= '<tr><td colspan="42">';
            $html .= $tableHelper->getView()->translate('No data found matching your criteria');
            $html .= '</td></tr>';
        }

        foreach ($rows as $row) {
            $html .= '<tr class="' . $trClass . '">';
            $html .= self::printTableRow($row, $tableHelper);
            $html .= "</tr>";
        }

        return $html;
    }

    /**
     * Wrapper foreach row in the table
     *
     * @param TableRowModel $tableRow
     * @param Table $tableHelper
     * @return string
     */
    public static function printTableRow(TableRowModel $tableRow, Table $tableHelper)
    {
        $html = '';

        /** @var TableHeaderCellModel $tableHeader */
        foreach ($tableHelper->getDisplayedHeaders() as $tableHeader) {
            $cell = $tableRow->getCell($tableHeader->getSafeName());
            if ($cell) {
                $html .= self::printTableCell($tableHelper->getDataStrategyResolver(), $cell);
            } else {
                $html .= self::printCustomTableCell($tableHeader, $tableRow);
//                    $html .= self::printMultiDeleteCheckbox($tableRow, $tableHelper->getTableModel());
                $var = 1;
            }
        }

        if (in_array('simpleSearch', $tableHelper->getDisplaySettings())) {
            /** @var TableFilterModel $filter */
            foreach ($tableHelper->getAdditionalFilters() as $filter) {
                $html .= self::printTableCell(
                    $tableHelper->getDataStrategyResolver(),
                    $filter->getInstance()->getFilterCellValue($tableRow)
                );
            }
        }

        if (in_array('actionRoutes', $tableHelper->getDisplaySettings())) {
            $links = $tableHelper->getTableModel()->getOptionRoutes();
            $links = self::getLinksFromListeners($tableHelper, $tableRow, $links);
            $html .= '<td class="kolom rowOptions"><span class="pull-right iconenNaarLinks">';
            foreach ($links as $action => $url) {
                $html .= self::getActionLink($action, $url, $tableRow->getCellValue('id'), $tableHelper);
            }
            $html .= '</span></td>';
        }

        return $html;
    }

    /**
     * Wraps the content cell in a <td> element
     *
     * @param $dataStrategyResolver
     * @param TableCellModel $cell
     * @param string $tdClass
     * @return string
     */
    public static function printTableCell(
        StrategyResolver $dataStrategyResolver,
        TableCellModel $cell,
        $tdClass = "kolom"
    ) {
        return
            sprintf("<td class=\"%s\">", $tdClass . " " . $cell->getSafeName()) .
            $dataStrategyResolver->resolveAndParse($cell->getValue(), $cell->getName()) .
            '</td>';
    }

    /**
     * Wraps the content cell in a <td> element
     *
     * @param TableHeaderCellModel $tableHeader
     * @param TableRowModel $tableRow
     * @param string $tdClass
     * @return string
     */
    public static function printCustomTableCell(
        TableHeaderCellModel $tableHeader,
        TableRowModel $tableRow,
        $tdClass = "kolom"
    ) {
        $tdClass = empty($tableHeader->getHtmlClass()) ? $tdClass : $tableHeader->getHtmlClass();
        $string =
            "<td class=\"%1\$s\">" .
            $tableHeader->getHtmlContent() .
            "</td>";
        return sprintf($string, $tdClass . ' ' . $tableHeader->getSafeName(), $tableRow->getCellValue('id'));
    }

    /**
     * Tries to add a glyph icon to an action link
     *
     * @param $action
     * @param $url
     * @param $id
     * @param Table $tableHelper
     * @return string
     */
    public static function getActionLink($action, $url, $id, Table $tableHelper)
    {
        $knownActions = array(
            'edit' => 'pencil',
            'delete' => 'trash',
            'view' => 'search',
        );

        if (in_array('noStyling', $tableHelper->getDisplaySettings()) || !array_key_exists($action, $knownActions)) {
            return sprintf(
                '<a class="options btn btn-mini %s" href="%s" title="%s" data-id="%s">%s</a>',
                $action,
                $tableHelper->getView()->url($url, array('action' => $action, 'id' => $id)),
                $action,
                $id,
                $action
            );
        }

        return sprintf(
            '<a href="%s" title="%s" data-id="%s"><i class="glyphicon glyphicon-%s icoonNaarLinks %s"></i></a>',
            $tableHelper->getView()->url($url, array('action' => $action, 'id' => $id)),
            $action,
            $id,
            $knownActions[$action],
            $action
        );
    }

    /**
     * @param Table $tableHelper
     * @param TableRowModel $tableRow
     * @param $links
     * @return mixed
     */
    public static function getLinksFromListeners(Table $tableHelper, TableRowModel $tableRow, $links)
    {
        $responseCollection = $tableHelper->getEventManager()->trigger(
            DataGridEvents::DATAGRID_PRE_PRINTTABLECONTENTROWACTIONS,
            __CLASS__,
            array('links' => $links, 'entity' => $tableRow)
        );
        foreach ($responseCollection as $response) {
            if (is_array($response) && isset($response['links'])) {
                $links = $response['links'];
            }
        }
        return $links;
    }
}
