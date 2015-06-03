<?php namespace Wms\Admin\DataGrid\View\Helper;

use Zend\Di\ServiceLocator;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\View\Helper\AbstractHelper;
use Wms\Admin\DataGrid\Model\TableModel;
use Wms\Admin\DataGrid\Fieldset\ColumnSettingsFieldset;
use Wms\Admin\DataGrid\Fieldset\FilterSettingsFieldset;
use Wms\Admin\DataGrid\View\Helper\DataStrategy\StrategyResolver;
use Zend\Escaper\Escaper;

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
        $displaySettings = array('columnsForm', 'pagination', 'ordering', 'simpleSearch', 'advancedSearch')
    ) {
        $this->escaper = new Escaper('utf-8');

        $this->setTableModel($tableModel);
        $this->displaySettings = $displaySettings;
        $this->dataStrategyResolver = new StrategyResolver();
        $this->dataStrategyResolver->addDependency($this->getView(), 'Zend\View\Renderer\RendererInterface');

        echo '<div class="datagrid before col-md-12">';
        $this->prepareForm();
        $this->prepareColumnSettings();
        $this->prepareFilterSettings();
        $this->printForm();
        echo '</div>';

        echo '<div class="datagrid table col-md-12">';
        $this->printTableStart();
        $this->printTableHeadRow();
        $this->printTableFilterRow();
        $this->printTableContent();
        $this->printTableEnd();
        echo '</div>';

        echo '<div class="datagrid after col-md-12">';
        $this->printPagination();
        echo '</div>';
    }

    /**
     * Prepares a new instance of \Zend\Form
     */
    public function prepareForm()
    {
        $this->settingsForm = new Form($this->getView()->Translate('settings'));
        $this->settingsForm->setAttribute('method', 'get');
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

    /**
     * If configured, this method will load the columnSettings fieldset in the form instance
     */
    public function prepareColumnSettings()
    {
        if (!in_array('columnsForm', $this->displaySettings)) {
            return;
        }
        $this->settingsForm->add(new ColumnSettingsFieldset($this->tableModel));
    }

    /**
     * If Configured, this method wil load the filters fieldset in the form instance
     */
    public function prepareFilterSettings()
    {
        if (!in_array('advancedSearch', $this->displaySettings)) {
            return;
        }
        $this->settingsForm->add(new FilterSettingsFieldset($this->tableModel));
    }

    /**
     * If configured, this will print the inline simple filter right after the
     * initial table heading.
     */
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

            $element = $this->dataStrategyResolver->displayFilterForDataType('search['.$tableHeader.']', $dataType);
            if ($element instanceof Element) {
                $element = $this->fillElementWithOptions($element, $tableHeader);
                echo $this->getView()->formElement($element);
            } else {
                echo $element;
            }
            echo '</td>';
        }
        if (in_array('simpleSearch', $this->displaySettings)) {
            echo '<td>
                <button type="submit" class="btn btn-primary max-width">
                    <span class="glyphicon glyphicon-search"></span> Search
                </button>
                </td>';
        }
        echo '</tr>';
        echo '</thead>';
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

        echo '<nav class="text-center"><ul class="pagination">';
        echo sprintf(
            '<li class="%s"><a href="%s" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>',
            $currentPage <= 1 ? 'disabled' : '',
            $this->getView()->UrlWithQuery(array('page' => ($currentPage - 1)))
        );

        for ($i = 1; $i <= $maxPages; $i++) {
            echo $i == $currentPage ? '<li class="active">' : '<li>';
            echo sprintf('<a href="%s">%d</a></li>', $this->getView()->UrlWithQuery(array('page' => $i)), $i);
        }

        echo sprintf(
            '<li class="%s"><a href="%s" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>',
            $currentPage >= $maxPages ? 'disabled' : '',
            $this->getView()->UrlWithQuery(array('page' => ($currentPage + 1)))
        );

        echo '</ul></nav>';
    }

    /**
     * if configured, Prints the "order by" icons in each table heading cell
     * @param $columName
     */
    protected function printOrderOption($columName)
    {
        if (!in_array('ordering', $this->displaySettings)) {
            return;
        }

        $downUrl = $this->getView()->UrlWithQuery(array('sort' => $columName, 'order' => 'desc'));
        $upUrl = $this->getView()->UrlWithQuery(array('sort' => $columName, 'order' => 'asc'));
        echo '<span class="pull-right">';
        echo '<a href="' . $downUrl . '" class="tabelHeadOpties"><i class="glyphicon glyphicon-chevron-down"></i></a>';
        echo '<a href="' . $upUrl . '" class="tabelHeadOpties"><i class="glyphicon glyphicon-chevron-up"></i></a>';
        echo '</span>';
    }

    /**
     * Prints the table headnig, containnign the named table columns
     *
     * @param string $classes
     */
    protected function printTableHeadRow($classes = "tabelHeader")
    {
        echo sprintf('<thead>', $classes);
        echo '<tr>';
        foreach ($this->getTableModel()->getUsedHeaders() as $column => $accessor) {
            if ($this->tableModel->isHiddenColumn($column)) {
                continue;
            }
            echo sprintf('<th class="%s">%s', $classes . " " . $column, $column);
            if ($column == $accessor) {
                $this->printOrderOption($column);
            }
            echo '</th>';
        }
        if (in_array('simpleSearch', $this->displaySettings)) {
            echo '<th class="rowOptions">Options</th>';
        }
        echo '</tr>';
    }

    /**
     * Looper for the table content
     *
     * @param string $tdClass
     */
    protected function printTableContent($tdClass = "kolom")
    {
        foreach ($this->getTableModel()->getRows() as $row) {
            $this->printTableContentRow($row);
        }
    }

    /**
     * Wrapper foreach row in the table
     *
     * @param array $rowData
     * @param string $trClass
     */
    protected function printTableContentRow(array $rowData, $trClass = "")
    {
        echo empty($trClass) ? "<tr>" : sprintf("<tr class=\"%s\"", $trClass);
        foreach ($rowData as $cellName => $cellValue) {
            if ($this->tableModel->isHiddenColumn($cellName)) {
                continue;
            }
            $this->printTableContentCell($cellValue, $cellName);
        }
        if (in_array('simpleSearch', $this->displaySettings)) {
            echo '<td></td>';
        }
        echo "</tr>";
    }

    /**
     * Wraps the content cell in a <td> element
     *
     * @param $cellValue
     * @param string $cellName
     * @param string $tdClass
     */
    protected function printTableContentCell($cellValue, $cellName = "", $tdClass = "kolom")
    {
        echo sprintf("<td class=\"%s\">", $tdClass . " " . $cellName);
        echo $this->dataStrategyResolver->resolveAndParse($cellValue, $cellName);
        echo '</td>';
    }

    /**
     * Prints the initial table element
     * @param string $classes
     */
    protected function printTableStart($classes = "table tabelVerkenner table-striped table-hover table-condensed")
    {
        if (in_array('simpleSearch', $this->displaySettings)) {
            echo '<form method="GET">';
        }
        echo sprintf('<table class="%s">', $classes);
    }

    /**
     * If the TableModel contains FilterData this method can provide the input element with the right optional values
     *
     * @param Element $element
     * @param $fieldName
     * @return Element
     */
    protected function fillElementWithOptions(Element $element, $fieldName)
    {
        // If the fieldname is not nested, there is no way the joined query returns data for you.
        $fieldNameSegments = explode(".", $fieldName);
        if (count($fieldNameSegments) <= 1) {
            return $element;
        }

        if (isset($this->tableModel->getAvailableFilterValues()[$fieldNameSegments[0]])) {
            $valueOptions = array();
            foreach ($this->tableModel->getAvailableFilterValues()[$fieldNameSegments[0]] as $filterValues) {
                $key = $fieldNameSegments[1];
                if (isset($filterValues[$key]) && !in_array($filterValues[$key], $valueOptions)) {
                    $valueOptions[$filterValues[$fieldNameSegments[1]]] = $filterValues[$fieldNameSegments[1]];
                }
            }

            if (method_exists($element, 'setValueOptions') && method_exists($element, 'setEmptyOption')) {
                $element->setEmptyOption(
                    $this->getView()->Translate('Select') . ' ' . $this->getView()->Translate($fieldName)
                );
                $element->setValueOptions($valueOptions);
            }
        }

        return $element;
    }

    /**
     * Closes the table by printing a </table> statement
     */
    protected function printTableEnd()
    {
        echo '</table>';
        // Any current column settings? pass them in the form
        if (in_array('simpleSearch', $this->displaySettings)) {
            if(isset($_GET['columns'])) {
                $value = $_GET['columns'];

                if(is_array($_GET['columns'])) {
                    $value = '[';
                    foreach($_GET['columns'] as $column) {
                        $value .= '"'.$column.'",';
                    }
                    $value = rtrim($value, ",").']';
                }

                echo sprintf(
                    "<input type='hidden' name='columns' value='%s'/>",
                    $this->escaper->escapeHtmlAttr($value)
                );
            }

            if(isset($_GET['sort']) && isset($_GET['order'])) {
                echo "<input type='hidden' name='sort' value='".$this->escaper->escapeHtmlAttr($_GET['sort'])."' />";
                echo "<input type='hidden' name='order' value='".$this->escaper->escapeHtmlAttr($_GET['order'])."' />";
            }

            echo '</form>';
        }
    }
}
