<?php namespace Wms\Admin\DataGrid\View\Helper;

use Zend\Di\ServiceLocator;
use Zend\Form\Element\MultiCheckbox;
use Zend\Form\Form;
use Zend\View\Helper\AbstractHelper;
use Wms\Admin\DataGrid\Model\TableModel;
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
     * @return TableModel
     */
    public function getTableModel()
    {
        return $this->tableModel;
    }

    /**
     * @param TableModel $tableModel
     */
    public function setTableModel($tableModel, $displaySettings = array('hiddenColumns', 'pagination', 'searchFilters'))
    {
        $this->tableModel = $tableModel;
        $this->displaySettings = $displaySettings;
    }

    /**
     * Execution of the view helper
     *
     * @param TableModel $tableModel
     * @return null|string
     */
    public function __invoke(TableModel $tableModel)
    {
        $this->setTableModel($tableModel);
        $this->dataStrategyResolver = new StrategyResolver();
        $this->dataStrategyResolver->addDependency($this->getView(), 'Zend\View\Renderer\RendererInterface');

        echo '<div class="datagrid-before">';
        $this->printColumnSettingsForm();
        echo '</div>';

        echo '<div class="datagrid-table">';
        $this->printTableStart();
        $this->printTableHeadRow();
        $this->printTableContent();
        $this->printTableEnd();
        echo '</div>';

        echo '<div class="datagrid-after">';
        $this->printPagination();
        echo '</div>';
    }

    public function printColumnSettingsForm()
    {
        if (!in_array('hiddenColumns', $this->displaySettings)) {
            return;
        }

        $columnSettingsForm = new Form('Display Settings');
        $columnSettingsForm->setAttribute('method', 'get');
        $columns = $this->getTableModel()->getAvailableHeaders();
        $checkboxName = 'columns';

        // Group checkboxes by property
        $columnGroups = array();
        foreach ($columns as $column) {
            $columnNameSegments = explode('.', $column);
            if (!array_key_exists($columnNameSegments[0], $columnGroups)) {
                $columnGroups[$columnNameSegments[0]] = array();
            }
        }

        // Create all values for each property
        foreach ($columns as $column) {
            $columnNameSegments = explode('.', $column);
            $label = $column;
            if (count($columnNameSegments) > 1) {
                $segments = $columnNameSegments;
                unset($segments[0]);
                $label = implode('.', $segments);
            }
            $valueOption = array(
                'value' => $column,
                'label' => $this->getView()->translate($label),
                'selected' => !$this->isHiddenColumn($column),
            );
            $columnGroups[$columnNameSegments[0]][] = $valueOption;
        }

        // Create the actuall form element per property
        foreach ($columnGroups as $property => $checkboxValues) {
            $multiCheckbox = new MultiCheckbox($checkboxName);
            $multiCheckbox->setOption('inline', false);
            if (count($checkboxValues) >= 2 || (count($checkboxValues) == 1 && (strpos($checkboxValues[0]['value'], ".") !== false))) {
                $multiCheckbox->setLabel($property);
            }
            $multiCheckbox->setValueOptions($checkboxValues);
            $columnSettingsForm->add($multiCheckbox);
        }

        // @todo: need to find a better way of implementing this ugly piece of code
        if (in_array('pagination', $this->displaySettings)) {
            $queryParams = array();
            parse_str(parse_url($this->getView()->ServerUrl(true), PHP_URL_QUERY), $queryParams);
            if (isset($queryParams['page']) && !is_null($queryParams['page']) && is_numeric($queryParams['page'])) {
                $columnSettingsForm->add(array(
                    'name' => 'page',
                    'type' => 'hidden',
                    'attributes' => array(
                        'value' => $this->getView()->EscapeUrl($queryParams['page']),
                    ),
                ));
            }
        }

        $columnSettingsForm->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => $this->getView()->translate('Apply'),
                'class' => 'btn',
            ),
        ));

        echo $this->getView()->DataGridForm($columnSettingsForm);
    }

    public function printPagination()
    {
        if (!in_array('pagination', $this->displaySettings)) {
            return;
        }

        $maxPages = $this->getTableModel()->getMaxPageNumber();
        $currentPage = $this->getTableModel()->getPageNumber();
        $currentUrl = strtok($this->getView()->ServerUrl(true), '?');

        // Unset any previous page parameter
        $queryParams = array();
        parse_str(parse_url($this->getView()->ServerUrl(true), PHP_URL_QUERY), $queryParams);
        unset($queryParams['page']);
        $queryParams = http_build_query($queryParams);

        echo '<nav><ul class="pagination">';

        $page = empty($queryParams) ? sprintf('page=%d', ($currentPage - 1)) : sprintf('&page=%d', ($currentPage - 1));
        echo sprintf(
            '<li class="%s"><a href="%s?%s" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>',
            $currentPage <= 1 ? 'disabled' : '', $currentUrl, $queryParams . $page
        );

        for ($i = 1; $i <= $maxPages; $i++) {
            $page = empty($queryParams) ? sprintf('page=%d', $i) : sprintf('&page=%d', $i);
            echo $i == $currentPage ? '<li class="active">' : '<li>';
            echo sprintf('<a href="%s?%s">%d</a></li>', $currentUrl, $queryParams . $page, $i);
        }

        $page = empty($queryParams) ? sprintf('page=%d', ($currentPage + 1)) : sprintf('&page=%d', ($currentPage + 1));
        echo sprintf(
            '<li class="%s"><a href="%s?%s" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>',
            $currentPage >= $maxPages ? 'disabled' : '', $currentUrl, $queryParams . $page
        );

        echo '</ul></nav>';

    }

    protected function printTableHeadRow($classes = "tabelHeader")
    {
        echo sprintf('<thead>', $classes);
        echo '<tr>';
        foreach ($this->getTableModel()->getUsedHeaders() as $column => $accessor) {
            if ($this->isHiddenColumn($column)) {
                continue;
            }
            echo sprintf('<th class="%s ">%s</th>', $classes . " " . $column, $column);
        }
        echo '</tr>';
        echo '</thead>';
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
            if ($this->isHiddenColumn($cellName)) {
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
        echo sprintf('<table class="%s">', $classes);
    }

    protected function printTableEnd()
    {
        echo '</table>';
    }

    private function isHiddenColumn($columnName)
    {
        if (array_key_exists($columnName, $this->getTableModel()->getUsedHeaders())) {
            return false;
        }

        return true;
    }
}
