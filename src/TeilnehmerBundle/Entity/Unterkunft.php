<?php

namespace TeilnehmerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Unterkunft
 *
 * @ORM\Table(name="unterkunft")
 * @ORM\Entity(repositoryClass="TeilnehmerBundle\Repository\UnterkunftRepository")
 */
class Unterkunft
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
     * @return Unterkunft
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

    public function __toString()
    {
        return $this->bezeichnung;
    }
}

