<?php

namespace TurnierplanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Hauptrundenspiel
 *
 * @ORM\Table(name="hauptrundenspiel")
 * @ORM\Entity(repositoryClass="TurnierplanBundle\Repository\HauptrundenspielRepository")
 */
class Hauptrundenspiel extends Spiel
{
    protected $discr = "hauptrundenspiel";

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(name="heimPlatz", type="integer")
     */
    private $heimPlatz;

    /**
     * @var int
     *
     * @ORM\Column(name="gastPlatz", type="integer")
     */
    private $gastPlatz;

    /**
     * @var int
     *
     * @ORM\Column(name="heimGruppe", type="integer")
     */
    private $heimGruppe;

    /**
     * @var int
     *
     * @ORM\Column(name="gastGruppe", type="integer")
     */
    private $gastGruppe;


    public function __construct($platz1, $gruppe1, $platz2, $gruppe2)
    {
        $this->heimPlatz = $platz1;
        $this->heimGruppe = $gruppe1;
        $this->gastPlatz = $platz2;
        $this->gastGruppe = $gruppe2;
    }


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
     * Set heimPlatz
     *
     * @param integer $heimPlatz
     *
     * @return Hauptrundenspiel
     */
    public function setHeimPlatz($heimPlatz)
    {
        $this->heimPlatz = $heimPlatz;

        return $this;
    }

    /**
     * Get heimPlatz
     *
     * @return int
     */
    public function getHeimPlatz()
    {
        return $this->heimPlatz;
    }

    /**
     * Set gastPlatz
     *
     * @param integer $gastPlatz
     *
     * @return Hauptrundenspiel
     */
    public function setGastPlatz($gastPlatz)
    {
        $this->gastPlatz = $gastPlatz;

        return $this;
    }

    /**
     * Get gastPlatz
     *
     * @return int
     */
    public function getGastPlatz()
    {
        return $this->gastPlatz;
    }

    /**
     * Set heimGruppe
     *
     * @param integer $heimGruppe
     *
     * @return Hauptrundenspiel
     */
    public function setHeimGruppe($heimGruppe)
    {
        $this->heimGruppe = $heimGruppe;

        return $this;
    }

    /**
     * Get heimGruppe
     *
     * @return int
     */
    public function getHeimGruppe()
    {
        return $this->heimGruppe;
    }

    /**
     * Set gastGruppe
     *
     * @param integer $gastGruppe
     *
     * @return Hauptrundenspiel
     */
    public function setGastGruppe($gastGruppe)
    {
        $this->gastGruppe = $gastGruppe;

        return $this;
    }

    /**
     * Get gastGruppe
     *
     * @return int
     */
    public function getGastGruppe()
    {
        return $this->gastGruppe;
    }
}

