<?php
/**
 * Created by PhpStorm.
 * User: Andreas
 * Date: 10.10.2016
 * Time: 14:53
 */

namespace TurnierplanBundle\Utils;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TurnierplanBundle\Entity\Endrundenspiel;
use TurnierplanBundle\Entity\Hauptrundenspiel;
use TurnierplanBundle\Entity\Vorrundenspiel;
use TurnierplanBundle\Entity\Spiel;
use TeilnehmerBundle\Entity\Mannschaft;

class SpieltagWrapper{

    private $spielTage = array();
    private $geschlecht;

    public function addSpieltag($spieltag)
    {
        array_push($this->spielTage,$spieltag);
    }

    public function getSpieltage()
    {
        return $this->spielTage;
    }

    public function getAnzSpieltage()
    {
        return count($this->spielTage);
    }

    public function setGeschlecht($geschlecht)
    {
        $this->geschlecht = $geschlecht;
    }

    public function getGeschlecht()
    {
        return $this->geschlecht;
    }

    public function getAnzSpiele()
    {
        $spiele = 0;
        foreach($this->spielTage as $spieltag)
        {
            $spiele += $spieltag->getAnzSpiele();
        }
        return $spiele;
    }

    public function getRestSpielTage()
    {
        return count($this->spielTage);
    }
}

?>