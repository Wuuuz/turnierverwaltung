<?php

namespace TeilnehmerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Mannschaft
 *
 * @ORM\Table(name="mannschaft")
 * @ORM\Entity(repositoryClass="TeilnehmerBundle\Repository\MannschaftRepository")
 */
class Mannschaft
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
     * @ORM\Column(name="name", type="string", length=255,nullable=true)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="gruppe", type="smallint",nullable=true)
     */
    private $gruppe;

    /**
     * @var int
     *
     * @ORM\Column(name="anzEssen", type="smallint")
     */
    private $anzEssen;

    /**
     * @var int
     *
     * @ORM\Column(name="anzSr", type="smallint")
     */
    private $anzSr;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ankunft", type="datetime")
     */
    private $ankunft;

    /**
     * @var int
     *
     * @ORM\Column(name="starke", type="smallint")
     */
    private $starke;

    /**
     * @var string
     *
     * @ORM\Column(name="mvName", type="string", length=255)
     */
    private $mvName;

    /**
     * @var string
     *
     * @ORM\Column(name="mvVorname", type="string", length=255)
     */
    private $mvVorname;

    /**
     * @var string
     *
     * @ORM\Column(name="mvPLZ", type="string", length=255)
     */
    private $mvPLZ;

    /**
     * @var string
     *
     * @ORM\Column(name="mvOrt", type="string", length=255)
     */
    private $mvOrt;

    /**
     * @var string
     *
     * @ORM\Column(name="mvLand", type="string", length=255)
     */
    private $mvLand;

    /**
     * @var string
     *
     * @ORM\Column(name="mvTelefon", type="string", length=255)
     */
    private $mvTelefon;

    /**
     * @var string
     *
     * @ORM\Column(name="mvEmail", type="string", length=255)
     */
    private $mvEmail;

    /**
     * @ORM\ManyToOne(targetEntity="Verein")
     * @ORM\JoinColumn(name="verein_id", referencedColumnName="id")
     */
    private $verein;

    /**
     * @ORM\ManyToOne(targetEntity="TurnierplanBundle\Entity\Altersklasse")
     * @ORM\JoinColumn(name="altersklasse_id", referencedColumnName="id")
     */
    private $altersklasse;

    /**
     * @ORM\ManyToOne(targetEntity="Unterkunft")
     * @ORM\JoinColumn(name="unterkunft_id", referencedColumnName="id")
     */
    private $unterkunft;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="smallint")
     */
    private $status;

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
     * Set name
     *
     * @param string $name
     *
     * @return Mannschaft
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
     * Set gruppe
     *
     * @param integer $gruppe
     *
     * @return Mannschaft
     */
    public function setGruppe($gruppe)
    {
        $this->gruppe = $gruppe;

        return $this;
    }

    /**
     * Get gruppe
     *
     * @return int
     */
    public function getGruppe()
    {
        return $this->gruppe;
    }

    /**
     * Set anzEssen
     *
     * @param integer $anzEssen
     *
     * @return Mannschaft
     */
    public function setAnzEssen($anzEssen)
    {
        $this->anzEssen = $anzEssen;

        return $this;
    }

    /**
     * Get anzEssen
     *
     * @return int
     */
    public function getAnzEssen()
    {
        return $this->anzEssen;
    }

    /**
     * Set anzSr
     *
     * @param integer $anzSr
     *
     * @return Mannschaft
     */
    public function setAnzSr($anzSr)
    {
        $this->anzSr = $anzSr;

        return $this;
    }

    /**
     * Get anzSr
     *
     * @return int
     */
    public function getAnzSr()
    {
        return $this->anzSr;
    }

    /**
     * Set ankunft
     *
     * @param \DateTime $ankunft
     *
     * @return Mannschaft
     */
    public function setAnkunft($ankunft)
    {
        $this->ankunft = $ankunft;

        return $this;
    }

    /**
     * Get ankunft
     *
     * @return \DateTime
     */
    public function getAnkunft()
    {
        return $this->ankunft;
    }

    /**
     * Set starke
     *
     * @param integer $starke
     *
     * @return Mannschaft
     */
    public function setStarke($starke)
    {
        $this->starke = $starke;

        return $this;
    }

    /**
     * Get starke
     *
     * @return int
     */
    public function getStarke()
    {
        return $this->starke;
    }

    /**
     * Set mvName
     *
     * @param string $mvName
     *
     * @return Mannschaft
     */
    public function setMvName($mvName)
    {
        $this->mvName = $mvName;

        return $this;
    }

    /**
     * Get mvName
     *
     * @return string
     */
    public function getMvName()
    {
        return $this->mvName;
    }

    /**
     * Set mvVorname
     *
     * @param string $mvVorname
     *
     * @return Mannschaft
     */
    public function setMvVorname($mvVorname)
    {
        $this->mvVorname = $mvVorname;

        return $this;
    }

    /**
     * Get mvVorname
     *
     * @return string
     */
    public function getMvVorname()
    {
        return $this->mvVorname;
    }

    /**
     * Set mvPLZ
     *
     * @param string $mvPLZ
     *
     * @return Mannschaft
     */
    public function setMvPLZ($mvPLZ)
    {
        $this->mvPLZ = $mvPLZ;

        return $this;
    }

    /**
     * Get mvPLZ
     *
     * @return string
     */
    public function getMvPLZ()
    {
        return $this->mvPLZ;
    }

    /**
     * Set mvOrt
     *
     * @param string $mvOrt
     *
     * @return Mannschaft
     */
    public function setMvOrt($mvOrt)
    {
        $this->mvOrt = $mvOrt;

        return $this;
    }

    /**
     * Get mvOrt
     *
     * @return string
     */
    public function getMvOrt()
    {
        return $this->mvOrt;
    }

    /**
     * Set mvLand
     *
     * @param string $mvLand
     *
     * @return Mannschaft
     */
    public function setMvLand($mvLand)
    {
        $this->mvLand = $mvLand;

        return $this;
    }

    /**
     * Get mvLand
     *
     * @return string
     */
    public function getMvLand()
    {
        return $this->mvLand;
    }

    /**
     * Set mvTelefon
     *
     * @param string $mvTelefon
     *
     * @return Mannschaft
     */
    public function setMvTelefon($mvTelefon)
    {
        $this->mvTelefon = $mvTelefon;

        return $this;
    }

    /**
     * Get mvTelefon
     *
     * @return string
     */
    public function getMvTelefon()
    {
        return $this->mvTelefon;
    }

    /**
     * Set mvEmail
     *
     * @param string $mvEmail
     *
     * @return Mannschaft
     */
    public function setMvEmail($mvEmail)
    {
        $this->mvEmail = $mvEmail;

        return $this;
    }

    /**
     * Get mvEmail
     *
     * @return string
     */
    public function getMvEmail()
    {
        return $this->mvEmail;
    }

    /**
     * Set verein
     *
     * @param \TeilnehmerBundle\Entity\Verein $verein
     *
     * @return Mannschaft
     */
    public function setVerein(\TeilnehmerBundle\Entity\Verein $verein = null)
    {
        $this->verein = $verein;

        return $this;
    }

    /**
     * Get verein
     *
     * @return \TeilnehmerBundle\Entity\Verein
     */
    public function getVerein()
    {
        return $this->verein;
    }

    /**
     * Set altersklasse
     *
     * @param \TurnierplanBundle\Entity\Altersklasse $altersklasse
     *
     * @return Mannschaft
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

    /**
     * Set unterkunft
     *
     * @param \TeilnehmerBundle\Entity\Unterkunft $unterkunft
     *
     * @return Mannschaft
     */
    public function setUnterkunft(\TeilnehmerBundle\Entity\Unterkunft $unterkunft = null)
    {
        $this->unterkunft = $unterkunft;

        return $this;
    }

    /**
     * Get unterkunft
     *
     * @return \TeilnehmerBundle\Entity\Unterkunft
     */
    public function getUnterkunft()
    {
        return $this->unterkunft;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Mannschaft
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }
}
