<?php

namespace TurnierplanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Turniertag
 *
 * @ORM\Table(name="turniertag")
 * @ORM\Entity(repositoryClass="TurnierplanBundle\Repository\TurniertagRepository")
 */
class Turniertag
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="uhrzBeginn", type="datetime")
     */
    private $uhrzBeginn;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="uhrzEnde", type="datetime")
     */
    private $uhrzEnde;


    /**
     * @ORM\ManyToOne(targetEntity="Spielplan")
     * @ORM\JoinColumn(name="spielplan_id", referencedColumnName="id")
     */
    private $spielplan;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set uhrzBeginn
     *
     * @param \DateTime $uhrzBeginn
     *
     * @return Turniertag
     */
    public function setUhrzBeginn($uhrzBeginn)
    {
        $this->uhrzBeginn = $uhrzBeginn;

        return $this;
    }

    /**
     * Get uhrzBeginn
     *
     * @return \DateTime
     */
    public function getUhrzBeginn()
    {
        return $this->uhrzBeginn;
    }

    /**
     * Set uhrzEnde
     *
     * @param string $uhrzEnde
     *
     * @return Turniertag
     */
    public function setUhrzEnde($uhrzEnde)
    {
        $this->uhrzEnde = $uhrzEnde;

        return $this;
    }

    /**
     * Get uhrzEnde
     *
     * @return string
     */
    public function getUhrzEnde()
    {
        return $this->uhrzEnde;
    }

    /**
     * Set spielplan
     *
     * @param \TurnierplanBundle\Entity\Spielplan $spielplan
     *
     * @return Turniertag
     */
    public function setSpielplan(\TurnierplanBundle\Entity\Spielplan $spielplan = null)
    {
        $this->spielplan = $spielplan;

        return $this;
    }

    /**
     * Get spielplan
     *
     * @return \TurnierplanBundle\Entity\Spielplan
     */
    public function getSpielplan()
    {
        return $this->spielplan;
    }
}
