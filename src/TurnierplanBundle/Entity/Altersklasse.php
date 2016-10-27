<?php

namespace TurnierplanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Altersklasse
 *
 * @ORM\Table(name="altersklasse")
 * @ORM\Entity(repositoryClass="TurnierplanBundle\Repository\AltersklasseRepository")
 */
class Altersklasse
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
     * @var string
     *
     * @ORM\Column(name="bezeichnung", type="string", length=255)
     */
    private $bezeichnung;

    /**
     * @var int
     *
     * @ORM\Column(name="spielmodus", type="smallint",nullable=true)
     */
    private $spielmodus;

    /**
     * @var int
     *
     * @ORM\Column(name="geschlecht", type="smallint")
     */
    private $geschlecht;


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
     * Set bezeichnung
     *
     * @param string $bezeichnung
     *
     * @return Altersklasse
     */
    public function setBezeichnung($bezeichnung)
    {
        $this->bezeichnung = $bezeichnung;

        return $this;
    }

    /**
     * Get bezeichnung
     *
     * @return string
     */
    public function getBezeichnung()
    {
        return $this->bezeichnung;
    }

    /**
     * Set spielmodus
     *
     * @param integer $spielmodus
     *
     * @return Altersklasse
     */
    public function setSpielmodus($spielmodus)
    {
        $this->spielmodus = $spielmodus;

        return $this;
    }

    /**
     * Get spielmodus
     *
     * @return int
     */
    public function getSpielmodus()
    {
        return $this->spielmodus;
    }

    /**
     * Set geschlecht
     *
     * @param integer $geschlecht
     *
     * @return Altersklasse
     */
    public function setGeschlecht($geschlecht)
    {
        $this->geschlecht = $geschlecht;

        return $this;
    }

    /**
     * Get geschlecht
     *
     * @return integer
     */
    public function getGeschlecht()
    {
        return $this->geschlecht;
    }
}
