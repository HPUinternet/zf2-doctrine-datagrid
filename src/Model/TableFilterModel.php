<?php namespace Wms\Admin\DataGrid\Model;

class TableFilterModel
{
    protected $name = '';
    protected $safeName = '';
    protected $header = null;
    protected $instance = null;
    protected $selectedValue = null;
    protected $availableValues = array();

    /**
     * Creates a new instance of the TableFilterModel
     *
     * @param $name
     * @param null $instance
     */
    public function __construct($name, $instance = null)
    {
        $this->setName($name);
        $this->setSafeName(str_replace(".", "", $name));
        $this->setInstance($instance);
    }

    /**
     * @return string
     */
    public function getSafeName()
    {
        return $this->safeName;
    }

    /**
     * @param string $safeName
     */
    public function setSafeName($safeName)
    {
        $this->safeName = $safeName;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * @param mixed $instance
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;
    }

    /**
     * @return mixed
     */
    public function getSelectedValue()
    {
        return $this->selectedValue;
    }

    /**
     * @param mixed $selectedValue
     */
    public function setSelectedValue($selectedValue)
    {
        $this->selectedValue = $selectedValue;
    }

    /**
     * @return array
     */
    public function getAvailableValues()
    {
        return $this->availableValues;
    }

    /**
     * @param array $availableValues
     */
    public function setAvailableValues($availableValues)
    {
        $this->availableValues = $availableValues;
    }

    /**
     * @return null
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param null $header
     */
    public function setHeader($header)
    {
        $this->header = $header;
    }
}
