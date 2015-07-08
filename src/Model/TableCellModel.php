<?php namespace Wms\Admin\DataGrid\Model;

class TableCellModel
{
    protected $name = '';
    protected $safeName = '';
    protected $accessor = '';
    protected $dataType = '';
    protected $visible = true;

    /**
     * Create's a new instance of the TableCellModel
     *
     * @param $name
     * @param $value
     */
    public function __construct($name, $value)
    {
        $nameSegments = explode('.', $name);
        $this->setName($name);
        $this->setSafeName(str_replace(".", "", $name));
        $this->setAccessor($nameSegments[0]);
        $this->setValue($value);
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return boolean
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * @param boolean $visible
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    /**
     * @return string
     */
    public function getSafeName()
    {
        return $this->safeName;
    }

    /**
     * @param $safeName
     */
    public function setSafeName($safeName)
    {
        $this->safeName = $safeName;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @param string $dataType
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;
    }

    /**
     * @return string
     */
    public function getAccessor()
    {
        return $this->accessor;
    }

    /**
     * @param string $accessor
     */
    public function setAccessor($accessor)
    {
        $this->accessor = $accessor;
    }
}
