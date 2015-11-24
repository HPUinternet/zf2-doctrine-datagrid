<?php namespace Wms\Admin\DataGrid\View\Helper\DataGrid;

use Zend\Di\ServiceLocator;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\View\Helper\AbstractHelper;
use Wms\Admin\DataGrid\Model\TableModel;
use Wms\Admin\DataGrid\Fieldset\ColumnSettingsFieldset;
use Wms\Admin\DataGrid\View\Helper\DataStrategy\StrategyResolver;
use Zend\Escaper\Escaper;

class Table extends AbstractHelper implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    /** @var array */
    protected $displayedHeaders = array();

    /** @var array */
    protected $additionalFilters = array();

    /** @var array */
    protected $displaySettings;

    /** @var TableModel */
    protected $tableModel;

    /** @var StrategyResolver */
    protected $dataStrategyResolver;

    /** @var Escaper */
    protected $escaper;

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

        $output = $this->printSettings();
        $output .= $this->printTable();
        $output .= $this->printPaginator();

        $this->addStylingToView();

        return $output;
    }

    /**
     * Prints the DataGridTable Zend Form instance by calling the DataGridForm helper.
     * @return string
     */
    protected function printSettings()
    {
        $settingsForm = $this->getSettingsForm();
        // @todo: need to find a better way of implementing this ugly piece of code
        if (in_array('pagination', $this->displaySettings)) {
            $queryParams = array();
            parse_str(parse_url($this->getView()->ServerUrl(true), PHP_URL_QUERY), $queryParams);
            if (isset($queryParams['page']) && !is_null($queryParams['page']) && is_numeric($queryParams['page'])) {
                $settingsForm->add(array(
                    'name' => 'page',
                    'type' => 'hidden',
                    'attributes' => array(
                        'value' => $this->getView()->EscapeUrl($queryParams['page']),
                    ),
                ));
            }
        }

        return
            '<div class="datagrid before col-md-12">' .
            $this->getView()->DataGridForm($settingsForm) .
            '</div>';
    }

    /**
     * Prepares a new instance of \Zend\Form
     *
     * @return Form
     */
    public function getSettingsForm()
    {
        $settingsForm = new Form($this->getView()->Translate('settings'));
        $settingsForm->setAttribute('method', 'get');
        if (!in_array('columnsForm', $this->displaySettings)) {
            return $settingsForm;
        }
        return $settingsForm->add(new ColumnSettingsFieldset($this->tableModel));
    }

    /**
     * @return string
     */
    protected function printTable()
    {
        $this->prepareTable();
        return
            '<div class="datagrid table col-md-12">' .
            TableHeader::printTableStart($this->displaySettings) .
            TableHeader::printTableHeadRow($this) .
            TableBody::printTableBody($this) .
            $this->printTableEnd() .
            '</div>';
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
     * @return string
     */
    protected function printPaginator()
    {
        $output = '<div class="datagrid after col-md-12">';
        if (in_array('pagination', $this->displaySettings)) {
            $output .= TablePaginator::printPagination($this);
        }
        $output .= '</div>';
        return $output;
    }

    /**
     * Appends / Prepends CSS and JS files for an enhanced UI/UX
     */
    protected function addStylingToView()
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
     * @return array
     */
    public function getDisplayedHeaders()
    {
        return $this->displayedHeaders;
    }

    /**
     * @param array $displayedHeaders
     */
    public function setDisplayedHeaders($displayedHeaders)
    {
        $this->displayedHeaders = $displayedHeaders;
    }

    /**
     * @return array
     */
    public function getAdditionalFilters()
    {
        return $this->additionalFilters;
    }

    /**
     * @return array
     */
    public function getDisplaySettings()
    {
        return $this->displaySettings;
    }

    /**
     * @return StrategyResolver
     */
    public function getDataStrategyResolver()
    {
        return $this->dataStrategyResolver;
    }
}
