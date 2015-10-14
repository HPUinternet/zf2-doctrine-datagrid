<?php namespace Wms\Admin\DataGrid\Tests\Bootstrap\Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity */
class Painting
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var String
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var String
     * @ORM\Column(type="string")
     */
    protected $field1;

    /**
     * @var String
     * @ORM\Column(type="string")
     */
    protected $field2;

    /**
     * @var String
     * @ORM\Column(type="string")
     */
    protected $field3;

    /**
     * @var String
     * @ORM\Column(type="string")
     */
    protected $field4;

    /**
     * @ORM\ManyToOne(targetEntity="Artist", inversedBy="paintings")
     * @ORM\JoinColumn(name="artist", referencedColumnName="id")
     **/
    protected $artist;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param String $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return String
     */
    public function getField1()
    {
        return $this->field1;
    }

    /**
     * @param String $field1
     */
    public function setField1($field1)
    {
        $this->field1 = $field1;
    }

    /**
     * @return String
     */
    public function getField2()
    {
        return $this->field2;
    }

    /**
     * @param String $field2
     */
    public function setField2($field2)
    {
        $this->field2 = $field2;
    }

    /**
     * @return String
     */
    public function getField3()
    {
        return $this->field3;
    }

    /**
     * @param String $field3
     */
    public function setField3($field3)
    {
        $this->field3 = $field3;
    }

    /**
     * @return String
     */
    public function getField4()
    {
        return $this->field4;
    }

    /**
     * @param String $field4
     */
    public function setField4($field4)
    {
        $this->field4 = $field4;
    }

    /**
     * @return mixed
     */
    public function getArtist()
    {
        return $this->artist;
    }

    /**
     * @param mixed $artist
     */
    public function setArtist($artist)
    {
        $this->artist = $artist;
    }
}