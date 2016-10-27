<?php

namespace TurnierplanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Endrundenspiel
 *
 * @ORM\Table(name="endrundenspiel")
 * @ORM\Entity(repositoryClass="TurnierplanBundle\Repository\EndrundenspielRepository")
 */
class Endrundenspiel extends Spiel
{
    protected $discr = "endrundenspiel";

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
     * @ORM\Column(name="gvFlag", type="smallint")
     */
    private $gvFlag;

    /**
    /**
     * @ORM\ManyToOne(targetEntity="Hauptrundenspiel")
     * @ORM\JoinColumn(name="hauptrundenspiel1_id", referencedColumnName="id")
     */
     
    private $hauptrundenspiel1;

    /**
     * @ORM\ManyToOne(targetEntity="Hauptrundenspiel")
     * @ORM\JoinColumn(name="hauptrundenspiel2_id", referencedColumnName="id")
     */
    private $hauptrundenspiel2;

    public function __construct($gvFlag, $hauptrundenspiel1, $hauptrundenspiel2)
    {
        $this->gvFlag = $gvFlag;
        $this->hauptrundenspiel1 = $hauptrundenspiel1;
        $this->hauptrundenspiel2 = $hauptrundenspiel2;
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
     * Set gvFlag
     *
     * @param integer $gvFlag
     *
     * @return Endrundenspiel
     */
    public function setGvFlag($gvFlag)
    {
        $this->gvFlag = $gvFlag;

        return $this;
    }

    /**
     * Get gvFlag
     *
     * @return int
     */
    public function getGvFlag()
    {
        return $this->gvFlag;
    }

    /**
     * Set hauptrundenspiel1
     *
     * @param \TurnierplanBundle\Entity\Hauptrundenspiel $hauptrundenspiel1
     *
     * @return Endrundenspiel
     */
    public function setHauptrundenspiel1(\TurnierplanBundle\Entity\Hauptrundenspiel $hauptrundenspiel1 = null)
    {
        $this->hauptrundenspiel1 = $hauptrundenspiel1;

        return $this;
    }

    /**
     * Get hauptrundenspiel1
     *
     * @return \TurnierplanBundle\Entity\Hauptrundenspiel
     */
    public function getHauptrundenspiel1()
    {
        return $this->hauptrundenspiel1;
    }

    /**
     * Set hauptrundenspiel2
     *
     * @param \TurnierplanBundle\Entity\Hauptrundenspiel $hauptrundenspiel2
     *
     * @return Endrundenspiel
     */
    public function setHauptrundenspiel2(\TurnierplanBundle\Entity\Hauptrundenspiel $hauptrundenspiel2 = null)
    {
        $this->hauptrundenspiel2 = $hauptrundenspiel2;

        return $this;
    }

    /**
     * Get hauptrundenspiel2
     *
     * @return \TurnierplanBundle\Entity\Hauptrundenspiel
     */
    public function getHauptrundenspiel2()
    {
        return $this->hauptrundenspiel2;
    }
}
