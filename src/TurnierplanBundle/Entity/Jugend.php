<?php

namespace TurnierplanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Jugend
 *
 * @ORM\Table(name="jugend")
 * @ORM\Entity(repositoryClass="TurnierplanBundle\Repository\JugendRepository")
 */
class Jugend
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
     * @ORM\Column(name="bezeichnung", type="string", length=255, unique=true)
     */
    private $bezeichnung;

    /**
     * @var string
     *
     * @ORM\Column(name="bezeichnungKurz", type="string", length=255, unique=true)
     */
    private $bezeichnungKurz;


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
     * @return Jugend
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
     * Set bezeichnungKurz
     *
     * @param string $bezeichnungKurz
     *
     * @return Jugend
     */
    public function setBezeichnungKurz($bezeichnungKurz)
    {
        $this->bezeichnungKurz = $bezeichnungKurz;

        return $this;
    }

    /**
     * Get bezeichnungKurz
     *
     * @return string
     */
    public function getBezeichnungKurz()
    {
        return $this->bezeichnungKurz;
    }
}

