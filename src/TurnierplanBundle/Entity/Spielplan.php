<?php

namespace TurnierplanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Spielplan
 *
 * @ORM\Table(name="spielplan")
 * @ORM\Entity(repositoryClass="TurnierplanBundle\Repository\SpielplanRepository")
 */
class Spielplan
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
     * @var int
     *
     * @ORM\Column(name="anzSpFeld", type="integer", nullable=false)
     */
    private $anzSpFeld;

    /**
     * @var int
     *
     * @ORM\Column(name="spielzeit", type="integer", nullable=true)
     */
    private $spielzeit;

    /**
     * @var int
     *
     * @ORM\Column(name="spielzeitAlt", type="integer", nullable=true)
     */
    private $spielzeitAlt;


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
     * Set anzSpFeld
     *
     * @param integer $anzSpFeld
     *
     * @return Spielplan
     */
    public function setAnzSpFeld($anzSpFeld)
    {
        $this->anzSpFeld = $anzSpFeld;

        return $this;
    }

    /**
     * Get anzSpFeld
     *
     * @return int
     */
    public function getAnzSpFeld()
    {
        return $this->anzSpFeld;
    }

    /**
     * Set spielzeit
     *
     * @param integer $spielzeit
     *
     * @return Spielplan
     */
    public function setSpielzeit($spielzeit)
    {
        $this->spielzeit = $spielzeit;

        return $this;
    }

    /**
     * Get spielzeit
     *
     * @return int
     */
    public function getSpielzeit()
    {
        return $this->spielzeit;
    }

    /**
     * Set spielzeitAlt
     *
     * @param integer $spielzeitAlt
     *
     * @return Spielplan
     */
    public function setSpielzeitAlt($spielzeitAlt)
    {
        $this->spielzeitAlt = $spielzeitAlt;

        return $this;
    }

    /**
     * Get spielzeitAlt
     *
     * @return int
     */
    public function getSpielzeitAlt()
    {
        return $this->spielzeitAlt;
    }
}

