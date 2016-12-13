<?php

namespace TurnierplanBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use TurnierplanBundle\Utils\VerteilungsWrapper;
use TurnierplanBundle\Entity\Vorrundenspiel;

/**
 * Class TurnierplanerstellungController
 * @package TurnierplanBundle\Controller
 */
class TurnierplanerstellungController extends Controller
{
    /**
     * Methode zum Ueberpruefen, ob gleichzeitig zwei Mannschaften des gleichen
     * Vereines spielen und ggf. Umverteilung dieser
     */
    private function pruefeVereinskollision($typ)
    {
        $spielplan = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Spielplan')
            ->findAll();
        $spielZeit = $spielplan[0]->getSpielzeit();

        $spiele = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Vorrundenspiel')
            ->findBy(array(),array('uhrzeit' => 'ASC'));

        $counter = 1; // Index fuer das naechste zu vergleichende Spiel

        //Ueberpruefung aller Spiele
        foreach ($spiele as $spiel)
        {
            $i = $counter; //i muss nach jedem Durchlauf wieder zurueckgesetzt werden

            //Alle Spiele mit gleicher Uhrzeit vergleichen
            while(isset($spiele[$i]) && $spiel->getUhrzeit() == $spiele[$i]->getUhrzeit())
            {
                //Kollision gefunden
                if($spiel->getMannschaft1()->getVerein() == $spiele[$i]->getMannschaft1()->getVerein() ||
                    $spiel->getMannschaft1()->getVerein() == $spiele[$i]->getMannschaft2()->getVerein() ||
                    $spiel->getMannschaft2()->getVerein() == $spiele[$i]->getMannschaft1()->getVerein() ||
                    $spiel->getMannschaft2()->getVerein() == $spiele[$i]->getMannschaft2()->getVerein())
                {
                    $this->sucheTauschbaresSpiel($spiel,$spielZeit,1);
                }
                $i++;
            }
            $counter++;
        }
    }

    /**
     * @param $spiel Spiel, welches getauscht werden soll
     * @param $spielZeit
     * @return bool Wurde das Spiel getauscht oder nicht
     */
    private function sucheTauschbaresSpiel($spiel, $spielZeit, $typ)
    {
        $uhrzeitTemp = clone $spiel->getUhrzeit();
        $spielZeitVergleich = $uhrzeitTemp->add(new \DateInterval("PT" . $spielZeit . "M"));

        $spiele = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Vorrundenspiel')
            ->findBy(array('uhrzeit' => $spiel->getUhrzeit()));

        $spieleVergleich = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Vorrundenspiel')
            ->findBy(array('uhrzeit' => $spielZeitVergleich));
        echo "spiele: <br>"; dump($spiele);
        echo "danach spiele: <br>"; dump($spieleVergleich);

        foreach($spieleVergleich as $spielVergleich) {

            //Vermeintlich tauschbares Spiel gefunden (nicht geprueft, ob durch Tauschen neue
            //Kollision entsteht
            if ($spiel->getMannschaft1()->getVerein() != $spielVergleich->getMannschaft1()->getVerein() &&
                $spiel->getMannschaft1()->getVerein() != $spielVergleich->getMannschaft2()->getVerein() &&
                $spiel->getMannschaft2()->getVerein() != $spielVergleich->getMannschaft1()->getVerein() &&
                $spiel->getMannschaft2()->getVerein() != $spielVergleich->getMannschaft2()->getVerein()
            ) {

                //Falls Spielfeldtreue -> tauschbare Spiele nur auf gleichem Feld
                if(($typ == 2 && $spiel->getSpielfeld() == $spielVergleich->getSpielfeld()) ||
                    ($typ == 1)) {
                    if (!$this->pruefeVereinsvorkommen($spieleVergleich, $spiel))
                        if (!$this->pruefeVereinsvorkommen($spiele, $spielVergleich)) {
                            $this->tauscheSpiele($spiel, $spielVergleich);
                            return true;
                        }
                }
            }
        }
        return false;
    }

    /**
     * Prueft, ob ein Verein aus einem Spiel in einem Spielearray vorhanden ist
     * @param $spiele Array von Spielen, in welchem gesucht werden soll
     * @param $spielVergleich Spiel, von welchem eine Vereinskollision in $spiele
     * gesucht werden soll
     * @return bool
     */
    private function pruefeVereinsvorkommen($spiele, $spielVergleich)
    {
        foreach($spiele as $spiel)
        {
            if($spiel->getMannschaft1()->getVerein() == $spielVergleich->getMannschaft1()->getVerein() &&
                $spiel->getMannschaft1()->getVerein() == $spielVergleich->getMannschaft2()->getVerein() &&
                $spiel->getMannschaft2()->getVerein() == $spielVergleich->getMannschaft1()->getVerein() &&
                $spiel->getMannschaft2()->getVerein() == $spielVergleich->getMannschaft2()->getVerein())
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Tauscht zwei Spiele im Spielplan (Uhrzeit, Turniertag und Feld)
     * @param $spiel1
     * @param $spiel2
     */
    private function tauscheSpiele($spiel1, $spiel2)
    {
        $em = $this->getDoctrine()->getManager();

        $uhrzeitTemp = $spiel1->getUhrzeit();
        $spielplatzTemp = $spiel1->getSpielfeld();
        $turniertagTemp = $spiel1->getTurniertag();

        $spiel1->setUhrzeit($spiel2->getUhrzeit());
        $spiel1->setSpielfeld($spiel2->getSpielfeld());
        $spiel1->setTurniertag($spiel2->getTurniertag());

        $spiel2->setUhrzeit($uhrzeitTemp);
        $spiel2->setSpielfeld($spielplatzTemp);
        $spiel2->setTurniertag($turniertagTemp);

        $em->persist($spiel1);
        $em->persist($spiel2);
        $em->flush();
    }
}