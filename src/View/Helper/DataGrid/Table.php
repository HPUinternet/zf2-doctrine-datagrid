<?php namespace Wms\Admin\DataGrid\View\Helper\DataGrid;

use Wms\Admin\DataGrid\Model\TableCellModel;
use Wms\Admin\DataGrid\Model\TableHeaderCellModel;
use Wms\Admin\DataGrid\Model\TableRowModel;
use Wms\Admin\DataGrid\Model\TableFilterModel;
use Zend\Di\ServiceLocator;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\View\Helper\AbstractHelper;
use Wms\Admin\DataGrid\Model\TableModel;
use Wms\Admin\DataGrid\Fieldset\ColumnSettingsFieldset;
use Wms\Admin\DataGrid\View\Helper\DataStrategy\StrategyResolver;
use Zend\Escaper\Escaper;

class Table extends AbstractHelper
{
    private $displayedHeaders = array();
    private $additionalFilters = array();
    private $addSearchColumn = false;

    /**
     * @var array
     */
    private $displaySettings;

    /**
     * @var TableModel;
     */
    private $tableModel;

    /**
     * @var StrategyResolver
     */
    private $dataStrategyResolver;

    /**
     * @var Form
     */
    private $settingsForm;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @return TableModel
     */
    public function getTableModel()
    {
        return $this->tableModel;
    }

    /**
     * @param TableModel $tableModel
     */
    public function setTableModel($tableModel)
    {
        $this->tableModel = $tableModel;
    }

    /**
     * Execution of the view helper
     *
     * @param TableModel $tableModel
     * @param array $displaySettings
     * @return null|string
     */
    public function __invoke(
        TableModel $tableModel,
        $displaySettings = array('columnsForm', 'pagination', 'ordering', 'simpleSearch', 'actionRoutes')
    ) {
        $this->escaper = new Escaper('utf-8');

        $this->setTableModel($tableModel);
        $this->displaySettings = $displaySettings;
        $this->dataStrategyResolver = new StrategyResolver($tableModel->getDataTypes());
        $this->dataStrategyResolver->addDependency($this->getView(), 'Zend\View\Renderer\RendererInterface');

        $output = '<div class="datagrid before col-md-12">';
        $this->prepareForm();
        $output .= $this->prepareForm();
        $output .= $this->printForm();
        $output .= '</div>';

        $output .= '<div class="datagrid table col-md-12">';
        $this->prepareTable();
        $output .= $this->printTableStart();
        $output .= $this->printTableHeadRow();
        $output .= $this->printTableContent();
        $output .= $this->printTableEnd();
        $output .= '</div>';

        $output .= '<div class="datagrid after col-md-12">';
        $output .= $this->printPagination();
        $output .= '</div>';

        $this->printStyling();

        return $output;
    }

    /**
     * Prepares a new instance of \Zend\Form
     */
    public function prepareForm()
    {
        $this->settingsForm = new Form($this->getView()->Translate('settings'));
        $this->settingsForm->setAttribute('method', 'get');
        if (!in_array('columnsForm', $this->displaySettings)) {
            return;
        }
        $this->settingsForm->add(new ColumnSettingsFieldset($this->tableModel));
    }

    /**
     * To prevent double foreach loops, pre render the columns in a private array
     */
    public function prepareTable()
    {
        foreach ($this->tableModel->getTableFilters() as $filter) {
            $header = $filter->getHeader();
            if (!is_null($header) && $header->isVisible()) {
                $this->displayedHeaders[] = $header;
                continue;
            }

            // Its a special filter, but only add it when it has an instance
            if (!is_null($filter->getInstance())) {
                $this->additionalFilters[$filter->getName()] = $filter;
            }
        }

        $this->addSearchColumn = (
            in_array('simpleSearch', $this->displaySettings) ||
            in_array('actionRoutes', $this->displaySettings)
        );
    }

    /**
     * Prints the DataGridTable Zend Form instance by calling the DataGridForm helper.
     */
    public function printForm()
    {
        // @todo: need to find a better way of implementing this ugly piece of code
        if (in_array('pagination', $this->displaySettings)) {
            $queryParams = array();
            parse_str(parse_url($this->getView()->ServerUrl(true), PHP_URL_QUERY), $queryParams);
            if (isset($queryParams['page']) && !is_null($queryParams['page']) && is_numeric($queryParams['page'])) {
                $this->settingsForm->add(array(
                    'name' => 'page',
                    'type' => 'hidden',
                    'attributes' => array(
                        'value' => $this->getView()->EscapeUrl($queryParams['page']),
                    ),
                ));
            }
        }

        return $this->getView()->DataGridForm($this->settingsForm);
    }

    /**
     * returns the opening table element
     * @param string $classes
     * @return string
     */
    protected function printTableStart($classes = "table tabelVerkenner table-striped table-hover table-condensed")
    {
        $html = '';
        if (in_array('simpleSearch', $this->displaySettings)) {
            $html .= '<form method="GET">';
        }
        $html .= sprintf('<table class="%s">', $classes);

        return $html;
    }

    /**
     * returns the table heading, containing the named table columns
     *
     * @param string $classes
     * @return string
     */
    protected function printTableHeadRow($classes = "tabelHeader")
    {
        $html = '<thead>';
        $html .= '<tr>';

        /** @var TableHeaderCellModel $tableHeader */
        foreach ($this->displayedHeaders as $tableHeader) {
            $html .= sprintf(
                '<th class="%s" ',
                $classes . " " . $tableHeader->getSafeName()
            );

            if ($tableHeader->getWidth() > 0) {
                $html .= 'style="width: ' . $tableHeader->getWidth() . 'px;"';
            }

            $html .= '>';
            $html .= $tableHeader->getName();

            if ($tableHeader->getName() == $tableHeader->getAccessor()) {
                $html .= $this->printOrderOption($tableHeader->getName());
            }

            $html .= '</th>';
        }

        /** @var TableFilterModel $filter */
        foreach ($this->additionalFilters as $filter) {
            $html .= sprintf(
                '<th class="%s customFilter">%s</th>',
                $classes . " " . $filter->getSafeName(),
                $filter->getName()
            );
        }

        if ($this->addSearchColumn) {
            $html .= '<th class="tabelHeader rowOptions"></th>';
        }

        $html .= $this->getView()->DataGridSearchFilter(
            $this->tableModel,
            $this->displayedHeaders,
            $this->additionalFilters,
            $this->dataStrategyResolver
        );

        $html .= '</tr>';
        $html .= '</thead>';

        return $html;
    }

    /**
     * Looper for the table content
     *
     * @param string $trClass
     * @return string
     */
    protected function printTableContent($trClass = "")
    {
        $html = '';
        foreach ($this->tableModel->getTableRows() as $row) {
            $html .= '<tr class="' . $trClass . '">';
            $html .= $this->printTableContentRow($row);
            $html .= "</tr>";
        }

        return $html;
    }

    /**
     * Wrapper foreach row in the table
     *
     * @param TableRowModel $tableRow
     * @return string
     */
    protected function printTableContentRow(TableRowModel $tableRow)
    {
        $html = '';

        /** @var TableHeaderCellModel $tableHeader */
        foreach ($this->displayedHeaders as $tableHeader) {
            $cell = $tableRow->getCell($tableHeader->getSafeName());
            if (!$cell) {
                $cell = null;
            }
            $html .= $this->printTableContentCell($cell);
        }

        if (in_array('simpleSearch', $this->displaySettings)) {
            /** @var TableFilterModel $filter */
            foreach ($this->additionalFilters as $filter) {
                $html .= $this->printTableContentCell($filter->getInstance()->getFilterCellValue($tableRow));
            }
        }

        if (in_array('actionRoutes', $this->displaySettings)) {
            $links = $this->tableModel->getOptionRoutes();
            $html .= '<td class="kolom rowOptions"><span class="pull-right iconenNaarLinks">';
            foreach ($links as $action => $url) {
                $html .= $this->getActionLink($action, $url, $tableRow->getCellValue('id'));
            }
            $html .= '</span></td>';
        }

        return $html;
    }

    /**
     * Wraps the content cell in a <td> element
     *
     * @param TableCellModel $cell
     * @param string $tdClass
     * @return string
     * @internal param $cellValue
     * @internal param string $cellName
     */
    protected function printTableContentCell(TableCellModel $cell = null, $tdClass = "kolom")
    {
        $html = '<td class="' . $tdClass . '">';
        if (!is_null($cell)) {
            $html = sprintf("<td class=\"%s\">", $tdClass . " " . $cell->getSafeName());
            $html .= $this->dataStrategyResolver->resolveAndParse($cell->getValue(), $cell->getName());
        }
        $html .= '</td>';

        return $html;
    }

    /**
     * if configured, Prints the "order by" icons in each table heading cell
     * @param $columName
     * @return string|void
     */
    protected function printOrderOption($columName)
    {
        if (!in_array('ordering', $this->displaySettings)) {
            return;
        }

        $downUrl = $this->getView()->UrlWithQuery(array('sort' => $columName, 'order' => 'desc'));
        $upUrl = $this->getView()->UrlWithQuery(array('sort' => $columName, 'order' => 'asc'));
        $iconClass = 'glyphicon glyphicon-chevron';
        $html = '<span class="pull-right">';
        $html .= '<a href="' . $downUrl . '" class="tabelHeadOpties"><i class="' . $iconClass . '-down"></i></a>';
        $html .= '<a href="' . $upUrl . '" class="tabelHeadOpties"><i class="' . $iconClass . '-up"></i></a>';
        $html .= '</span>';

        return $html;
    }


    /**
     * Closes the table by printing a </table> statement
     */
    protected function printTableEnd()
    {
        $html = '</table>';
        // Any current column settings? pass them in the form
        if (in_array('simpleSearch', $this->displaySettings)) {
            if (isset($_GET['columns'])) {
                $value = $_GET['columns'];

                if (is_array($_GET['columns'])) {
                    $value = '[';
                    foreach ($_GET['columns'] as $column) {
                        $value .= '"' . $column . '",';
                    }
                    $value = rtrim($value, ",") . ']';
                }

                $html .= sprintf(
                    "<input type='hidden' name='columns' value='%s'/>",
                    $this->escaper->escapeHtmlAttr($value)
                );
            }

            if (isset($_GET['sort']) && isset($_GET['order'])) {
                $html .= "<input type='hidden' name='sort' value='"
                    . $this->escaper->escapeHtmlAttr($_GET['sort']) . "' />";
                $html .= "<input type='hidden' name='order' value='"
                    . $this->escaper->escapeHtmlAttr($_GET['order']) . "' />";
            }

            $html .= '</form>';
        }

        return $html;
    }

    /**
     * Appends / Prepends CSS and JS files for an enhanced UI/UX
     */
    protected function printStyling()
    {
        if (!in_array('noStyling', $this->displaySettings)) {
            $this->view->headLink()->prependStylesheet(
                $this->view->basePath() . '/css/Admin/DataGrid/Dist/dataGrid.css'
            );

            $this->view->headScript()->appendFile(
                $this->view->basePath() . '/js/Admin/DataGrid/Dist/dataGrid.js',
                'text/javascript'
            );
        }
    }

    /**
     * Tries to add a glyph icon to an action link
     *
     * @param $action
     * @param $url
     * @param $id
     * @return string
     */
    protected function getActionLink($action, $url, $id)
    {
        $knownActions = array(
            'edit' => 'pencil',
            'delete' => 'trash',
            'view' => 'search',
        );

        if (in_array('noStyling', $this->displaySettings) || !array_key_exists($action, $knownActions)) {
            return sprintf(
                '<a class="options btn btn-mini" href="%s" title="%s">%s</a>',
                $this->view->url($url, array('action' => $action, 'id' => $id)),
                $action
            );
        }

        return sprintf(
            '<a href="%s" title="%s"><i class="glyphicon glyphicon-%s icoonNaarLinks"></i></a>',
            $this->view->url($url, array('action' => $action, 'id' => $id)),
            $action,
            $knownActions[$action]
        );
    }

    /**
     * If configured, prints the table pagination to navigate to a next set of data
     */
    public function printPagination()
    {
        if (!in_array('pagination', $this->displaySettings)) {
            return;
        }

        $maxPages = $this->getTableModel()->getMaxPageNumber();
        $currentPage = $this->getTableModel()->getPageNumber();

        $html = '<nav class="text-center"><ul class="pagination">';
        $html .= sprintf(
            '<li class="%s"><a href="%s" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>',
            $currentPage <= 1 ? 'disabled' : '',
            $this->getView()->UrlWithQuery(array('page' => ($currentPage - 1)))
        );

        for ($i = 1; $i <= $maxPages; $i++) {
            $html .= $i == $currentPage ? '<li class="active">' : '<li>';
            $html .= sprintf('<a href="%s">%d</a></li>', $this->getView()->UrlWithQuery(array('page' => $i)), $i);
        }

        $html .= sprintf(
            '<li class="%s"><a href="%s" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>',
            $currentPage >= $maxPages ? 'disabled' : '',
            $this->getView()->UrlWithQuery(array('page' => ($currentPage + 1)))
        );

        $html .= '</ul></nav>';

        return $html;
    }
}
