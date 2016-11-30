<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Roe
 *
 * @ORM\Table(name="role")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\RoleRepository")
 */
class Role
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
     * @ORM\Column(name="techBezeichnung", type="string", length=255, unique=true)
     */
    private $techBezeichnung;


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
     * @return Roe
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
     * Set techBezeichnung
     *
     * @param string $techBezeichnung
     *
     * @return Roe
     */
    public function setTechBezeichnung($techBezeichnung)
    {
        $this->techBezeichnung = $techBezeichnung;

        return $this;
    }

    /**
     * Get techBezeichnung
     *
     * @return string
     */
    public function getTechBezeichnung()
    {
        return $this->techBezeichnung;
    }
}

