<?php namespace Wms\Admin\DataGrid\View\Helper;

use Zend\Di\ServiceLocator;
use Zend\Form\Element\MultiCheckbox;
use Zend\Form\Form;
use Zend\View\Helper\AbstractHelper;
use Wms\Admin\DataGrid\Model\TableModel;
use IntlDateFormatter;
use DateTime;

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

        $this->printColumnSettingsForm();

        $this->printTableStart();
        $this->printTableHeadRow();
        $this->printTableContent();
        $this->printTableEnd();

        $this->printPagination();
    }

    public function printColumnSettingsForm()
    {
        if (!in_array('hiddenColumns', $this->displaySettings)) {
            return;
        }

        $columnSettingsForm = new Form();
        $columnSettingsForm->setAttribute('method', 'get');
        $valueOptions = array();
        $displayedColumns = new MultiCheckbox('datagrid_columns');
        $displayedColumns->setLabel($this->view->translate('Show data'));

        foreach ($this->getTableModel()->getAvailableHeaders() as $columnName) {
            $valueOption = array(
                'value' => $columnName,
                'label' => $this->view->translate($columnName),
                'selected' => !$this->isHiddenColumn($columnName),
            );
            $valueOptions[] = $valueOption;
        }

        $displayedColumns->setValueOptions($valueOptions);
        $columnSettingsForm->add($displayedColumns);
        $columnSettingsForm->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => $this->view->translate('Apply'),
                'class' => 'btn',
            ),
        ));

        echo $this->view->form($columnSettingsForm);
    }

    public function printPagination()
    {
        if (!in_array('pagination', $this->displaySettings)) {
            return;
        }

        $maxPages = $this->getTableModel()->getMaxPageNumber();
        $currentPage = $this->getTableModel()->getPageNumber();
        $currentUrl = strtok($this->view->ServerUrl(true), '?');

        // Unset any previous page parameter
        $queryParams = array();
        parse_str(parse_url($this->view->ServerUrl(true), PHP_URL_QUERY), $queryParams);
        unset($queryParams['page']);
        $queryParams = http_build_query($queryParams);

        echo '<nav><ul class="pagination">';
        for ($i = 0; $i < $maxPages; $i++) {
            $page = empty($queryParams) ? sprintf('page=%d', $i) : sprintf('&page=%d', $i);
            echo $i == $currentPage ? '<li class="active">' : '<li>';
            echo sprintf('<a href="%s?%s">%d</a></li>', $currentUrl, $queryParams . $page, $i);
        }
        echo '</ul></nav>';

    }

    protected function printTableHeadRow($classes = "tabelHeader")
    {
        echo sprintf('<thead>', $classes);
        echo '<tr>';
        foreach ($this->getTableModel()->getUsedHeaders() as $column) {
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
        switch (true) {
            case is_bool($cellValue) || (($cellValue === 1 || $cellValue === 0) && strpos($cellName, 'id') === false):
                $cellValue = $cellValue == true ? "yes" : "no";
                echo $this->view->translate($cellValue);
                break;
            case $cellValue === null || $cellValue === "":
                echo "&nbsp;";
                break;
            case is_array($cellValue) == true:
                if (is_array(reset($cellValue))) {
                    echo '<ol>';
                    foreach ($cellValue as $cellArray) {
                        echo '<li>';
                        echo implode(", ", $cellArray);
                        echo '</li>';
                    }
                    echo '</ol>';
                    break;
                }
                echo implode(", ", $cellValue);
                break;
            case $cellValue instanceof DateTime:
                echo $this->view->dateFormat(
                    $cellValue,
                    IntlDateFormatter::MEDIUM,
                    IntlDateFormatter::NONE,
                    $this->view->formLabel()->getTranslator()->getLocale()
                );
                break;
            default:
                echo $cellValue;
                break;
        }
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
        if (in_array($columnName, $this->getTableModel()->getUsedHeaders())) {
            return false;
        }

        return true;
    }
}
