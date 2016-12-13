<?php

namespace TeilnehmerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Schiedsrichter
 *
 * @ORM\Table(name="schiedsrichter")
 * @ORM\Entity(repositoryClass="TeilnehmerBundle\Repository\SchiedsrichterRepository")
 */
class Schiedsrichter
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Verein")
     * @ORM\JoinColumn(name="verein_id", referencedColumnName="id")
     */
    private $verein;
    
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
     * @return Schiedsrichter
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
     * Set verein
     *
     * @param \TeilnehmerBundle\Entity\Verein $verein
     *
     * @return Schiedsrichter
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
}
