<?php
/**
 * Created by PhpStorm.
 * User: Andreas
 * Date: 10.10.2016
 * Time: 14:53
 */

namespace TurnierplanBundle\Entity;

class Spieltag{

    private $spiele = array();

    function addSpiel($spiel){
        array_push($this->spiele, $spiel);
    }

    function addSpiele($spiele){
        $this->spiele = array_merge($this->spiele,$spiele);
    }

    function getSpiele()
    {
        return $this->spiele;
    }

    public function getAnzSpiele()
    {
        return count($this->spiele);
    }

    function getStaerkstesSpiel(){
        if(isset($this->spiele[0])){
            $staerkstesSpiel = $this->spiele[0];
            for($i=1; $i < count($this->spiele); $i++)
                if($staerkstesSpiel->getStaerke() < $this->spiele[$i]->getStaerke())
                    $staerkstesSpiel = $this->spiele[$i];
            return $staerkstesSpiel;
        }
        return null;
    }
}

?>