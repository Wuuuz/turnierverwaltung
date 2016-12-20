<?php

namespace TurnierplanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * Spiel
 *
 * @ORM\Table(name="spiel")
 * @ORM\Entity(repositoryClass="TurnierplanBundle\Repository\SpielRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"spiel" = "Spiel", "vorrundenspiel" = "Vorrundenspiel", "hauptrundenspiel" = "Hauptrundenspiel", "endrundenspiel" = "Endrundenspiel",})
 */
class Spiel
{
    protected $discr = "spiel";

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="uhrzeit", type="datetime",nullable=true)
     */
    private $uhrzeit;

    /**
     * @var int
     *
     * @ORM\Column(name="uhrzeitAlt", type="integer",nullable=true)
     */
    private $uhrzeitAlt;

    /**
     * @var int
     *
     * @ORM\Column(name="spielfeld", type="smallint",nullable=true)
     */
    private $spielfeld;

    /**
     * @var int
     *
     * @ORM\Column(name="spielfeldAlt", type="smallint",nullable=true)
     */
    private $spielfeldAlt;

    /**
     * @var int
     *
     * @ORM\Column(name="spielnummer", type="smallint",nullable=true)
     */
    private $spielnummer;

    /**
     * @var int
     *
     * @ORM\Column(name="ergHeim", type="smallint",nullable=true)
     */
    private $ergHeim;

    /**
     * @var int
     *
     * @ORM\Column(name="ergGast", type="smallint",nullable=true)
     */
    private $ergGast;


    /**
     * @var int
     *
     * @ORM\Column(name="platzUm", type="integer",nullable=true)
     */
    private $platzUm;

    /**
     * @ORM\ManyToOne(targetEntity="Turniertag")
     * @ORM\JoinColumn(name="turniertag_id", referencedColumnName="id",nullable=true)
     */
    private $turniertag;

    /**
     * @ORM\ManyToOne(targetEntity="TeilnehmerBundle\Entity\Mannschaft")
     * @ORM\JoinColumn(name="mannschaft1_id", referencedColumnName="id",nullable=true)
     */
    private $mannschaft1;

    /**
     * @ORM\ManyToOne(targetEntity="TeilnehmerBundle\Entity\Mannschaft")
     * @ORM\JoinColumn(name="mannschaft2_id", referencedColumnName="id",nullable=true)
     */
    private $mannschaft2;

    /**
 * @ORM\ManyToOne(targetEntity="TeilnehmerBundle\Entity\Schiedsrichter")
 * @ORM\JoinColumn(name="schiedsrichter1_id", referencedColumnName="id",nullable=true)
 */
    private $schiedsrichter1;

    /**
     * @ORM\ManyToOne(targetEntity="TeilnehmerBundle\Entity\Schiedsrichter")
     * @ORM\JoinColumn(name="schiedsrichter2_id", referencedColumnName="id",nullable=true)
     */
    private $schiedsrichter2;

    /**
     * @ORM\ManyToOne(targetEntity="Altersklasse")
     * @ORM\JoinColumn(name="altersklasse_id", referencedColumnName="id")
     */
    private $altersklasse;

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
     * Set uhrzeit
     *
     * @param \DateTime $uhrzeit
     *
     * @return Spiel
     */
    public function setUhrzeit($uhrzeit)
    {
        $this->uhrzeit = $uhrzeit;

        return $this;
    }

    /**
     * Get uhrzeit
     *
     * @return \DateTime
     */
    public function getUhrzeit()
    {
        return $this->uhrzeit;
    }

    /**
     * Set uhrzeitAlt
     *
     * @param \DateTime $uhrzeitAlt
     *
     * @return Spiel
     */
    public function setUhrzeitAlt($uhrzeitAlt)
    {
        $this->uhrzeitAlt = $uhrzeitAlt;

        return $this;
    }

    /**
     * Get uhrzeitAlt
     *
     * @return \DateTime
     */
    public function getUhrzeitAlt()
    {
        return $this->uhrzeitAlt;
    }

    /**
     * Set spielfeld
     *
     * @param integer $spielfeld
     *
     * @return Spiel
     */
    public function setSpielfeld($spielfeld)
    {
        $this->spielfeld = $spielfeld;

        return $this;
    }

    /**
     * Get spielfeld
     *
     * @return int
     */
    public function getSpielfeld()
    {
        return $this->spielfeld;
    }

    /**
     * Set spielfeldAlt
     *
     * @param integer $spielfeldAlt
     *
     * @return Spiel
     */
    public function setSpielfeldAlt($spielfeldAlt)
    {
        $this->spielfeldAlt = $spielfeldAlt;

        return $this;
    }

    /**
     * Get spielfeldAlt
     *
     * @return int
     */
    public function getSpielfeldAlt()
    {
        return $this->spielfeldAlt;
    }

    /**
     * Set spielnummer
     *
     * @param integer $spielnummer
     *
     * @return Spiel
     */
    public function setSpielnummer($spielnummer)
    {
        $this->spielnummer = $spielnummer;

        return $this;
    }

    /**
     * Get spielnummer
     *
     * @return int
     */
    public function getSpielnummer()
    {
        return $this->spielnummer;
    }

    /**
     * Set ergHeim
     *
     * @param integer $ergHeim
     *
     * @return Spiel
     */
    public function setErgHeim($ergHeim)
    {
        $this->ergHeim = $ergHeim;

        return $this;
    }

    /**
     * Get ergHeim
     *
     * @return int
     */
    public function getErgHeim()
    {
        return $this->ergHeim;
    }

    /**
     * Set ergGast
     *
     * @param string $ergGast
     *
     * @return Spiel
     */
    public function setErgGast($ergGast)
    {
        $this->ergGast = $ergGast;

        return $this;
    }

    /**
     * Get ergGast
     *
     * @return string
     */
    public function getErgGast()
    {
        return $this->ergGast;
    }

    /**
     * Set platzUm
     *
     * @param integer $platzUm
     *
     * @return Spiel
     */
    public function setPlatzUm($platzUm)
    {
        $this->platzUm = $platzUm;

        return $this;
    }

    /**
     * Get platzUm
     *
     * @return integer
     */
    public function getPlatzUm()
    {
        return $this->platzUm;
    }

    /**
     * Set turniertag
     *
     * @param \TurnierplanBundle\Entity\Turniertag $turniertag
     *
     * @return Spiel
     */
    public function setTurniertag(\TurnierplanBundle\Entity\Turniertag $turniertag = null)
    {
        $this->turniertag = $turniertag;

        return $this;
    }

    /**
     * Get turniertag
     *
     * @return \TurnierplanBundle\Entity\Turniertag
     */
    public function getTurniertag()
    {
        return $this->turniertag;
    }

    /**
     * Set mannschaft1
     *
     * @param \TeilnehmerBundle\Entity\Mannschaft $mannschaft1
     *
     * @return Spiel
     */
    public function setMannschaft1(\TeilnehmerBundle\Entity\Mannschaft $mannschaft1 = null)
    {
        $this->mannschaft1 = $mannschaft1;

        return $this;
    }

    /**
     * Get mannschaft1
     *
     * @return \TeilnehmerBundle\Entity\Mannschaft
     */
    public function getMannschaft1()
    {
        return $this->mannschaft1;
    }

    /**
     * Set mannschaft2
     *
     * @param \TeilnehmerBundle\Entity\Mannschaft $mannschaft2
     *
     * @return Spiel
     */
    public function setMannschaft2(\TeilnehmerBundle\Entity\Mannschaft $mannschaft2 = null)
    {
        $this->mannschaft2 = $mannschaft2;

        return $this;
    }

    /**
     * Get mannschaft2
     *
     * @return \TeilnehmerBundle\Entity\Mannschaft
     */
    public function getMannschaft2()
    {
        return $this->mannschaft2;
    }

    /**
     * Set schiedsrichter1
     *
     * @param \TeilnehmerBundle\Entity\Schiedsrichter $schiedsrichter1
     *
     * @return Spiel
     */
    public function setSchiedsrichter1(\TeilnehmerBundle\Entity\Schiedsrichter $schiedsrichter1 = null)
    {
        $this->schiedsrichter1 = $schiedsrichter1;

        return $this;
    }

    /**
     * Get schiedsrichter1
     *
     * @return \TeilnehmerBundle\Entity\Schiedsrichter
     */
    public function getSchiedsrichter1()
    {
        return $this->schiedsrichter1;
    }

    /**
     * Set schiedsrichter2
     *
     * @param \TeilnehmerBundle\Entity\Schiedsrichter $schiedsrichter2
     *
     * @return Spiel
     */
    public function setSchiedsrichter2(\TeilnehmerBundle\Entity\Schiedsrichter $schiedsrichter2 = null)
    {
        $this->schiedsrichter2 = $schiedsrichter2;

        return $this;
    }

    /**
     * Get schiedsrichter2
     *
     * @return \TeilnehmerBundle\Entity\Schiedsrichter
     */
    public function getSchiedsrichter2()
    {
        return $this->schiedsrichter2;
    }

    /**
     * Set altersklasse
     *
     * @param \TurnierplanBundle\Entity\Altersklasse $altersklasse
     *
     * @return Spiel
     */
    public function setAltersklasse(\TurnierplanBundle\Entity\Altersklasse $altersklasse = null)
    {
        $this->altersklasse = $altersklasse;

        return $this;
    }

    /**
     * Get altersklasse
     *
     * @return \TurnierplanBundle\Entity\Altersklasse
     */
    public function getAltersklasse()
    {
        return $this->altersklasse;
    }

    public function getStaerke(){
        return $this->mannschaft1->getStarke() + $this->mannschaft2->getStarke();
    }
}
