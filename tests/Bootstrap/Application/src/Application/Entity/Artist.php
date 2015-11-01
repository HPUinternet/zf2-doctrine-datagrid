<?php namespace Wms\Admin\DataGrid\Tests\Bootstrap\Application\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity */
class Artist
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
     * @ORM\OneToMany(targetEntity="Painting", mappedBy="artist")
     **/
    protected $paintings;

    public function __construct()
    {
        $this->paintings = new ArrayCollection();
    }

    /**
     * @return int
     */
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
     * @return mixed
     */
    public function getPaintings()
    {
        return $this->paintings;
    }

    /**
     * @param mixed $paintings
     */
    public function setPaintings($paintings)
    {
        $this->paintings = $paintings;
    }
}