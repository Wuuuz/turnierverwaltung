<?php

namespace TurnierplanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Vorrundenspiel
 *
 * @ORM\Table(name="vorrundenspiel")
 * @ORM\Entity(repositoryClass="TurnierplanBundle\Repository\VorrundenspielRepository")
 */
class Vorrundenspiel extends Spiel
{
    protected $discr = "vorrundenspiel";

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}

