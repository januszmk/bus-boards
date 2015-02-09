<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BusLine
 *
 * @ORM\Table(name="bus_line")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BusLineRepository")
 */
class BusLine
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;



    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return BusLine
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set stopAt
     *
     * @param \DateTime $stopAt
     * @return BusLine
     */
    public function setStopAt($stopAt)
    {
        $this->stopAt = $stopAt;

        return $this;
    }


}
