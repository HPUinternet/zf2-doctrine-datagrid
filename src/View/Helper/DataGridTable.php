<?php namespace Wms\Admin\DataGrid\View\Helper;

use Zend\Di\ServiceLocator;
use Zend\View\Helper\AbstractHelper;
use Wms\Admin\DataGrid\Model\TableModel;

class DataGridTable extends AbstractHelper
{
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
    public function setTableModel($tableModel)
    {
        $this->tableModel = $tableModel;
    }

    /**
     * Execution of the view helper
     *
     * @return null|string
     */
    public function __invoke(TableModel $tableModel)
    {
        $this->setTableModel($tableModel);
        $this->printTableStart();
        $this->printTableHeadRow();
        $this->printTableContent();
        $this->printTableEnd();
    }

    protected function printTableHeadRow($classes = "tabelHeader") {
        echo sprintf('<thead>', $classes);
        echo '<tr>';
        foreach($this->getTableModel()->getHeaderRow() as $column) {
            echo sprintf('<th class="%s %s">%s</th>',$classes, $classes.$column['fieldName'], $column['fieldName']);
        }
        echo '</tr>';
        echo '</thead>';
    }

    protected function printTableContent($tdClass = "kolom") {
        foreach($this->getTableModel()->getRows() as $row) {
            echo sprintf('<tr>');
//            var_dump($row);
            foreach($row as $name => $value) {
                echo sprintf('<td class="%s">', $tdClass);
                var_dump($value);
                echo '</td>';
            }
            echo sprintf('</tr>');
        }
    }
    
    protected function printTableStart($classes = "table tabelVerkenner table-striped table-hover table-condensed") {
        echo sprintf('<table class="%s">', $classes);
    }

    protected function printTableEnd() {
        echo '</table>';
    }
}
