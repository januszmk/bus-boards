<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BusEntry
 *
 * @ORM\Table(name="bus_entry")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BusEntryRepository")
 */
class BusEntry
{
    //types of entries
    const TYPE_NORMAL = 'normal';
    const TYPE_SATURDAY = 'saturday';
    const TYPE_SUNDAY = 'sunday';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="stop_at", type="time")
     */
    private $stopAt;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var BusLine
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\BusLine")
     * @ORM\JoinColumn(name="bus_line", referencedColumnName="id")
     */
    private $busLine;


    /**
     * @var BusStop
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\BusStop")
     * @ORM\JoinColumn(name="bus_stop", referencedColumnName="id")
     */
    private $busStop;

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
     * Set type
     *
     * @param string $type
     * @return BusEntry
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return BusLine
     */
    public function getBusLine()
    {
        return $this->busLine;
    }

    /**
     * @param BusLine $busLine
     * @return BusEntry
     */
    public function setBusLine(BusLine $busLine)
    {
        $this->busLine = $busLine;

        return $this;
    }

    /**
     * @return BusStop
     */
    public function getBusStop()
    {
        return $this->busStop;
    }

    /**
     * @param BusStop $busStop
     * @return BusEntry
     */
    public function setBusStop(BusStop $busStop)
    {
        $this->busStop = $busStop;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStopAt()
    {
        return $this->stopAt;
    }

    /**
     * @param \DateTime $stopAt
     */
    public function setStopAt(\DateTime $stopAt )
    {
        $this->stopAt = $stopAt;
    }
}
