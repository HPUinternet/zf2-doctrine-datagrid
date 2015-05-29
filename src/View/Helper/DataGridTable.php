<?php namespace Wms\Admin\DataGrid\View\Helper;

use Zend\Di\ServiceLocator;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\View\Helper\AbstractHelper;
use Wms\Admin\DataGrid\Model\TableModel;
use Wms\Admin\DataGrid\Fieldset\ColumnSettingsFieldset;
use Wms\Admin\DataGrid\Fieldset\FilterSettingsFieldset;
use Wms\Admin\DataGrid\View\Helper\DataStrategy\StrategyResolver;

class DataGridTable extends AbstractHelper
{
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
        $displaySettings = array('columnsForm', 'pagination', 'ordering', 'simpleSearch', 'advancedSearch')
    ) {
        $this->setTableModel($tableModel);
        $this->displaySettings = $displaySettings;
        $this->dataStrategyResolver = new StrategyResolver();
        $this->dataStrategyResolver->addDependency($this->getView(), 'Zend\View\Renderer\RendererInterface');

        echo '<div class="datagrid-before col-md-12">';
        $this->prepareForm();
        $this->prepareColumnSettings();
        $this->prepareFilterSettings();
        $this->printForm();
        echo '</div>';

        echo '<div class="datagrid-table col-md-12">';
        $this->printTableStart();
        $this->printTableHeadRow();
        $this->printTableFilterRow();
        $this->printTableContent();
        $this->printTableEnd();
        echo '</div>';

        echo '<div class="datagrid-after col-md-12">';
        $this->printPagination();
        echo '</div>';
    }

    public function prepareForm()
    {
        $this->settingsForm = new Form($this->getView()->Translate('settings'));
        $this->settingsForm->setAttribute('method', 'get');
    }

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

        $this->settingsForm->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => $this->getView()->translate('Apply'),
                'class' => 'btn btn-primary',
            ),
        ));
        echo $this->getView()->DataGridForm($this->settingsForm);
    }

    public function prepareColumnSettings()
    {
        if (!in_array('columnsForm', $this->displaySettings)) {
            return;
        }
        $this->settingsForm->add(New ColumnSettingsFieldset($this->tableModel));
    }

    public function prepareFilterSettings()
    {
        if (!in_array('advancedSearch', $this->displaySettings)) {
            return;
        }
        $this->settingsForm->add(New FilterSettingsFieldset($this->tableModel));
    }

    public function printTableFilterRow()
    {
        if (!in_array('simpleSearch', $this->displaySettings)) {
            echo '</thead>';

            return;
        }
        echo '<tr class="simpleSearch">';

        /**
         * 1. kijk of er een speciale filter verklaard is voor dat veld, gebruik die
         * 2. // TODO: kijk of het een doctrine extension is, gebruik die
         * 3. kijk wat de datatype is, spuug een filter uit aan de hand van de type
         */
        foreach ($this->tableModel->getUsedHeaders() as $tableHeader => $accessor) {
            echo '<td>';
            $dataType = $this->tableModel->getDataTypeByHeader($tableHeader);
            if ($tableHeader != $accessor) {
                $dataType = "Array";
            }

            $element = $this->dataStrategyResolver->displayFilterForDataType($tableHeader, $dataType);
            if ($element instanceof Element) {

                echo $this->getView()->formElement($element);
            } else {
                echo $element;
            }
            echo '</td>';
        }
        echo '</tr>';
        echo '</thead>';

    }

    public function printPagination()
    {
        if (!in_array('pagination', $this->displaySettings)) {
            return;
        }

        $maxPages = $this->getTableModel()->getMaxPageNumber();
        $currentPage = $this->getTableModel()->getPageNumber();

        echo '<nav class="text-center"><ul class="pagination">';
        echo sprintf('<li class="%s"><a href="%s" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>',
            $currentPage <= 1 ? 'disabled' : '',
            $this->getView()->UrlWithQuery(array('page' => ($currentPage - 1)))
        );

        for ($i = 1; $i <= $maxPages; $i++) {
            echo $i == $currentPage ? '<li class="active">' : '<li>';
            echo sprintf('<a href="%s">%d</a></li>', $this->getView()->UrlWithQuery(array('page' => $i)), $i);
        }

        echo sprintf('<li class="%s"><a href="%s" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>',
            $currentPage >= $maxPages ? 'disabled' : '',
            $this->getView()->UrlWithQuery(array('page' => ($currentPage + 1)))
        );

        echo '</ul></nav>';

    }

    protected function printOrderOption($columName)
    {
        $sortDownUrl = $this->getView()->UrlWithQuery(array('sort' => $columName, 'order' => 'desc'));
        $sortUpUrl = $this->getView()->UrlWithQuery(array('sort' => $columName, 'order' => 'asc'));
        echo '<span class="pull-right">';
        echo "<a href=\"$sortDownUrl\" class=\"tabelHeadOpties\"><i class=\"glyphicon glyphicon-chevron-down\"></i></a>";
        echo "<a href=\"$sortUpUrl\" class=\"tabelHeadOpties\"><i class=\"glyphicon glyphicon-chevron-up\"></i></a>";
        echo '</span>';
    }

    protected function printTableHeadRow($classes = "tabelHeader")
    {
        echo sprintf('<thead>', $classes);
        echo '<tr>';
        foreach ($this->getTableModel()->getUsedHeaders() as $column => $accessor) {
            if ($this->tableModel->isHiddenColumn($column)) {
                continue;
            }
            echo sprintf('<th class="%s">%s', $classes . " " . $column, $column);
            if (in_array('ordering', $this->displaySettings) && $column == $accessor) {
                $this->printOrderOption($column);
            }
            echo '</th>';
        }
        echo '</tr>';
    }

    protected function printTableContent($tdClass = "kolom")
    {
        foreach ($this->getTableModel()->getRows() as $row) {
            $this->printTableContentRow($row);
        }
    }

    protected function printTableContentRow(array $rowData, $trClass = "")
    {
        echo empty($trClass) ? "<tr>" : sprintf("<tr class=\"%s\"", $trClass);
        foreach ($rowData as $cellName => $cellValue) {
            if ($this->tableModel->isHiddenColumn($cellName)) {
                continue;
            }
            $this->printTableContentCell($cellValue, $cellName);
        }
        echo "</tr>";
    }

    protected function printTableContentCell($cellValue, $cellName = "", $tdClass = "kolom")
    {
        echo sprintf("<td class=\"%s\">", $tdClass . " " . $cellName);
        echo $this->dataStrategyResolver->resolveAndParse($cellValue, $cellName);
        echo '</td>';
    }

    protected function printTableStart($classes = "table tabelVerkenner table-striped table-hover table-condensed")
    {
        if (in_array('simpleSearch', $this->displaySettings)) {
            echo '<form method="GET" action="' . $this->getView()->UrlWithQuery(array('page' => 0)) . '">';
        }
        echo sprintf('<table class="%s">', $classes);
    }

    protected function printTableEnd()
    {
        echo '</table>';
        if (in_array('simpleSearch', $this->displaySettings)) {
            echo '</form>';
        }
    }
}
