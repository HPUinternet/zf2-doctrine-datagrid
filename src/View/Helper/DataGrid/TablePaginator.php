<?php namespace Wms\Admin\DataGrid\View\Helper\DataGrid;


use Zend\Di\ServiceLocator;
use Zend\Form\Element;

abstract class TablePaginator
{
    /**
     * If configured, prints the table pagination to navigate to a next set of data
     * @param Table $tableHelper
     * @return string
     */
    public static function printPagination(Table $tableHelper)
    {
        $maxPages = $tableHelper->getTableModel()->getMaxPageNumber();
        $currentPage = $tableHelper->getTableModel()->getPageNumber();

        $html = '<nav class="text-center"><ul class="pagination">';
        $html .= sprintf(
            '<li class="%s"><a href="%s" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>',
            $currentPage <= 1 ? 'disabled' : '',
            $tableHelper->getView()->UrlWithQuery(array('page' => ($currentPage - 1)))
        );

        for ($i = 1; $i <= $maxPages; $i++) {
            $html .= $i == $currentPage ? '<li class="active">' : '<li>';
            $html .= sprintf('<a href="%s">%d</a></li>', $tableHelper->getView()->UrlWithQuery(array('page' => $i)), $i);
        }

        $html .= sprintf(
            '<li class="%s"><a href="%s" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>',
            $currentPage >= $maxPages ? 'disabled' : '',
            $tableHelper->getView()->UrlWithQuery(array('page' => ($currentPage + 1)))
        );

        $html .= '</ul></nav>';

        return $html;
    }
}
