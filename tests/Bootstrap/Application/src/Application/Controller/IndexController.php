<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Wms\Admin\DataGrid\Tests\Bootstrap\Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * @method \Wms\Admin\DataGrid\Controller\Plugin\DataGridPlugin DataGridPlugin()
 */
class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel(array(
            'table' => $this->DataGridPlugin()->getTable($this->params()->fromQuery())
        ));
    }
}
