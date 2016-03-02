<?php namespace Wms\Admin\DataGrid\View\Form;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\FormCollection as BaseFormCollectionHelper;

/**
 * TODO: make recusrive
 *
 * Class NestedSettings
 * @package Wms\Admin\DataGrid\View\Form
 */
class NestedSettings extends BaseFormCollectionHelper
{
    private $count = 1;

    protected $columnWidth = 2;

    /**
     * @inheritdoc
     */
    public function __invoke(ElementInterface $fieldset = null, $labelPosition = null)
    {
        if (!$fieldset) {
            return $this;
        }

        $wrapper = '';

        if ($this->count === 1) {
            $wrapper .= ' <div class="row">';
        }

        $label = '<h2>' . $fieldset->getName() . '</h2>';

        $wrapper .= '<div class="col-md-' . $this->columnWidth . '">' . $label . '%s</div>';

        if ($this->count === 6) {
            $wrapper .= '</div>';
            $this->count = 0;
        }
        $this->count = $this->count + 1;

        $content = '';

        foreach ($fieldset->getIterator() as $item) {
            $content .= $this->getView()->formRow($item);
        }

        return sprintf($wrapper, $content);
    }
}
