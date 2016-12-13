<?php

namespace TurnierplanBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use TurnierplanBundle\Entity\Altersklasse;
use TurnierplanBundle\Entity\Endrundenspiel;
use TurnierplanBundle\Entity\Hauptrundenspiel;
use TurnierplanBundle\Utils\SpieltagWrapper;
use TurnierplanBundle\Utils\VerteilungsWrapper;
use TurnierplanBundle\Entity\Vorrundenspiel;
use TeilnehmerBundle\Entity\Mannschaft;
use TurnierplanBundle\Entity\Spieltag;

/**
 * Class TurnierplanerstellungController
 * @package TurnierplanBundle\Controller
 */
class TurnierplanerstellungController extends Controller
{
    /**
     * @Route("/turnierplan/neu/spieleVerteilen")
     */
    public function spieleVerteilenAction()
    {
        $anzSp = 2;
        $typ = 2;

        $altersklassen = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Altersklasse')
            ->findAll();

        $spielWrappers = array();
        $gesamtSpiele = 0;
        $gesamtZeit = 0;
        $spielZeit = 0;

        for($i = 0; $i < count($altersklassen); $i++)
        {
            $spielWrappers[$i] = $this->erzeugeSpieltagWrapper($altersklassen[$i]);
            $gesamtSpiele += $spielWrappers[$i]->getAnzSpiele();
        }

        $verteilungsArray = $this->erzeugeVerteilungsArray($spielWrappers,$anzSp,$typ);

        $turniertage = $this->getDoctrine()
        ->getRepository('TurnierplanBundle:Turniertag')
        ->findBy(array(),array('uhrzBeginn' => 'ASC'));

        $anfangsZeit = $turniertage[0]->getUhrzBeginn();
        $anfangsZeitTemp = clone $anfangsZeit;

        if($typ == 1) {
            //$spielZeit = $anzSp*$gesamtZeit/$gesamtSpiele;
            $this->verteileSpieleAufFelder($verteilungsArray,0,2,15,$anfangsZeit, $turniertage);
        } // Spiele gleichmäßig auf Felder verteilt ohne Spielfeldtreue
        if($typ == 2) //Gleichmäßige Verteilung mit Spielfeldtreue
        {
            for($i = 0; $i < count($verteilungsArray); $i++) {
                $this->verteileSpieleAufFelder($verteilungsArray[$i], $i, 1, 8,$anfangsZeit,$turniertage);
                $anfangsZeit = $anfangsZeitTemp;
            }
        }

        return $this->render('TurnierplanBundle:Default:index.html.twig');
    }

    /**
     * Erzeugt einen Wrapper mit allen zu verteilenden Spieltagen fuer
     * eine bestimmte Alterklasse
     * @param $altersklasse
     * @return SpieltagWrapper
     */
    public function erzeugeSpieltagWrapper($altersklasse)
    {
        $anzMann = count($this->getDoctrine()
            ->getRepository('TeilnehmerBundle:Mannschaft')
            ->findBy(array('altersklasse' => $altersklasse)));

        // STEP 1: VORRUNDENSPIELE AUF SPIELTAGE VERTEILEN //

        $vorrundenspiele = $this->getDoctrine()
                ->getRepository('TurnierplanBundle:Vorrundenspiel')
                ->findBy(array('altersklasse' => $altersklasse));

        $spieltagWrapper = new SpieltagWrapper();
        $spieltagWrapper->setGeschlecht($altersklasse->getGeschlecht());

        //Falls zwei Gruppen existieren, muessen Vorrundenspiele gemixt werden
        if($altersklasse->getSpielmodus() == 1 || $altersklasse->getSpielmodus() == 2)
        {
            $vorrundenspiele1 = array();
            $vorrundenspiele2 = array();

            //Spiele nach Gruppen aufteilen, damit das Mixen moeglich ist
            foreach($vorrundenspiele as $vorrundenspiel)
            {
                if($vorrundenspiel->getMannschaft1()->getGruppe() == '1')
                    array_push($vorrundenspiele1, $vorrundenspiel);
                else
                    array_push($vorrundenspiele2, $vorrundenspiel);
            }

            //Anzahl der Mannschaften und Spieltage berechnen
            $anzMann1 = count($this->getDoctrine()
                ->getRepository('TeilnehmerBundle:Mannschaft')
                ->findBy(array('altersklasse' => $altersklasse, 'gruppe' => 1)));

            $anzSpProSpt1 = floor($anzMann1/2);

            $anzSpProSpt2 = floor(count($this->getDoctrine()
                    ->getRepository('TeilnehmerBundle:Mannschaft')
                    ->findBy(array('altersklasse' => $altersklasse, 'gruppe' => 2)))/2);

            //Spieltage erzeugen
            while(count($vorrundenspiele1) >= $anzSpProSpt1)
            {
                $neuerSpieltag = new Spieltag();
                $neuerSpieltag->addSpiele(array_splice($vorrundenspiele1,0,$anzSpProSpt1));
                $spieltagWrapper->addSpieltag($neuerSpieltag);

                if(isset($vorrundenspiele2[0]))
                {
                    $neuerSpieltag = new Spieltag();
                    $neuerSpieltag->addSpiele(array_splice($vorrundenspiele2,0,$anzSpProSpt2));
                    $spieltagWrapper->addSpieltag($neuerSpieltag);
                }
            }
        }
        else //falls es nur eine Gruppe gibt
        {
            $anzSpProSpt = floor($anzMann/2);

            while(count($vorrundenspiele) >= $anzSpProSpt){
                $neuerSpieltag = new Spieltag();
                $neuerSpieltag->addSpiele(array_splice($vorrundenspiele,0,$anzSpProSpt));
                $spieltagWrapper->addSpieltag($neuerSpieltag);
            }
        }

        // STEP 2: PLATZIERUNGSSPIELE VERTEILEN //

        if($altersklasse->getSpielmodus() == 2 || $altersklasse->getSpielmodus() == 3)
        {
            if($anzMann%4 == 2) {
                $neueGruppenSpiele = $this->getDoctrine()
                    ->getRepository('TurnierplanBundle:Hauptrundenspiel')
                    ->findBy(array('altersklasse' => $altersklasse, 'platzUm' => $anzMann-1));
            }
            else if($anzMann%4 == 3) {
                $neueGruppenSpiele = $this->getDoctrine()
                    ->getRepository('TurnierplanBundle:Hauptrundenspiel')
                    ->findBy(array('altersklasse' => $altersklasse, 'platzUm' => $anzMann-2));
            }
        }

        $hauptrundenSpieltag = new Spieltag();

        //Ggf. eines der neuen Gruppenspiele einfuegen
        if(isset($neueGruppenSpiele[0]))
        {
            $hauptrundenSpieltag->addSpiel($neueGruppenSpiele[0]);
        }

        //Alle Hauptrundenspiele zum Spieltag
        $hauptrundenSpiele = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Hauptrundenspiel')
            ->findBy(array('altersklasse' => $altersklasse),array('platzUm' => 'DESC'));

        $anzHauptrundenSpiele = count($hauptrundenSpiele);

        //Entfernt alle 2er bzw 3er-Gruppen-Spiele (bei Spielmodus 2 oder 3)
        if($altersklasse->getSpielmodus() == 2 || $altersklasse->getSpielmodus() == 3) {
            for ($i = 0; $i < $anzHauptrundenSpiele; $i++)
                if (($hauptrundenSpiele[$i]->getPlatzUm() == $anzMann - 1) || ($hauptrundenSpiele[$i]->getPlatzUm() == $anzMann - 2))
                    unset($hauptrundenSpiele[$i]);
        }

        $hauptrundenSpieltag->addSpiele($hauptrundenSpiele);
        $spieltagWrapper->addSpieltag($hauptrundenSpieltag);

        //Falls 3er-Runde gespielt wird
        if(isset($neueGruppenSpiele[1]) && isset($neueGruppenSpiele[2]))
        {
            $zwischenSpieltag = new Spieltag();
            $zwischenSpieltag->addSpiel($neueGruppenSpiele[1]);
            $spieltagWrapper->addSpieltag($zwischenSpieltag);
        }

        $endrundenSpieltag = new Spieltag();

        //Ggf. eines der neuen Gruppenspiele einfuegen
        if(isset($neueGruppenSpiele[1]) && !isset($neueGruppenSpiele[2]))
        {
            $endrundenSpieltag->addSpiel($neueGruppenSpiele[1]);
        }
        else if(isset($neueGruppenSpiele[1]) && isset($neueGruppenSpiele[2]))
        {
            $endrundenSpieltag->addSpiel($neueGruppenSpiele[2]);
        }

        //Alle Endrundenspiele zum Spieltag
        $endrundenSpieltag->addSpiele($this->getDoctrine()
            ->getRepository('TurnierplanBundle:Endrundenspiel')
            ->findBy(array('altersklasse' => $altersklasse),array('platzUm' => 'DESC')));

        if($endrundenSpieltag->getAnzSpiele()>0)
            $spieltagWrapper->addSpieltag($endrundenSpieltag);

        return $spieltagWrapper;
    }

    /**
     * Erzeugt ein mehrdimensionales Array, welches pro Spielfeld alle
     * SpielWrapper enthaelt, welche darauf verteilt werden sollen
     * @param $spielWrappers Alle erzeugten SpielWrapper
     * @param $anzFelder
     * @param $typ Modus, wie SpieltagWrapper verteilt werden
     * @return array $verteilungsArray
     */
    public function erzeugeVerteilungsArray($spieltagWrappers, $anzFelder, $typ)
    {
        $gesamtSpiele = 0;
        $bisherigeSpiele = 0;
        $verteilungsArray = array();

        usort($spieltagWrappers, array($this, "cmpVerteilerWrapperGe"));

        foreach ($spieltagWrappers as $spieltagWrapper)
            $gesamtSpiele += $spieltagWrapper->getAnzSpiele();


        //Keine Beachtung der Spielfeldtreue
        if($typ == 1)
            return $this->mischeSpieltage($spieltagWrappers);

        //Gleichmäßige Aufteilung auf die Spielfelder mit Spielfeldtrue
        else if ($typ == 2) {
            $verteilungsArrayGemischt = array();
            $aktuellerSpWrapper = 0;

            for ($i = 1; $i <= $anzFelder; $i++) {
                $verteilungsArray[$i - 1] = array();
                $counter = 0;

                while ($bisherigeSpiele < ($i * $gesamtSpiele / $anzFelder)) {
                    $verteilungsArray[$i - 1][$counter] = $spieltagWrappers[$aktuellerSpWrapper];
                    $bisherigeSpiele += $spieltagWrappers[$aktuellerSpWrapper]->getAnzSpiele();
                    $counter++;
                    $aktuellerSpWrapper++;
                }
            }

            for($i = 0; $i < count($verteilungsArray); $i++)
                $verteilungsArrayGemischt[$i] = $this->mischeSpieltage($verteilungsArray[$i]);

            return $verteilungsArrayGemischt;
        }

        //Maennlich/weiblich getrennt, gemischte Jugenden gleichmäßg verteilen
        else if($typ == 3)
        {

        }

        return $verteilungsArray;
    }

    /**
     * Mischt für eine Menge von SpieltagWrappern die Spieltage gleichmäig
     * zusammen
     * @param $spieltagWrappers
     * @return array
     */
    private function mischeSpieltage($spieltagWrappers)
    {
        $verteilungsArray = array();

        foreach($spieltagWrappers as $spieltagWrapper){

            $spielTage = $spieltagWrapper->getSpieltage();

            //Erster SpieltagWrapper wird als Anfangsarray verwendet
            if(!isset($verteilungsArray[0])) {
                $verteilungsArray = $spielTage;
            }
            else
            {
                $spieleKoeffizient = count($verteilungsArray)/count($spielTage);
                $naechsterSpieltag = 0;

                //Gleichmäßige Verteilung
                for($i = 0; $i < count($verteilungsArray); $i++)
                {
                    if((count($verteilungsArray)-$i)/(count($spielTage)-$naechsterSpieltag) < $spieleKoeffizient)
                    {
                        //Einfuegen in das Array
                        array_splice($verteilungsArray,$i,0,array($spielTage[$naechsterSpieltag]));
                        $naechsterSpieltag++;

                        //Wenn alle verteilt sind, Abbruch
                        if((count($spielTage)-$naechsterSpieltag) == 0)
                            break;
                    }
                }
            }
        }

        return $verteilungsArray;
    }

    /**
     * Verteilt eine Anzahl von Spieltag in einem VerteilungsArray auf eine
     * bestimmte Anzahl von Spielfeldern
     * @param $verteilungsArray Spieltage pro Felder
     * @param $anfangFeld Erster Feld zum Besetzen
     * @param $anzFelder Anzahl der Felder
     * @param $turniertage Zu besetzende Turniertage
     * @param $spielZeit Spielzeit
     */
    private function verteileSpieleAufFelder($verteilungsArray, $anfangFeld, $anzFelder, $spielZeit, $anfangsZeit, $turniertage)
    {
        $gesamtZeit = 0;

        foreach($turniertage as $turniertag)
            $gesamtZeit += abs($turniertag->getUhrzEnde()->getTimestamp() - $turniertag->getUhrzBeginn()->getTimestamp())/60;

        $derzTurniertagID = 0;
        $derzSpielzeit = $anfangsZeit;
        $endSpielzeit = $turniertage[$derzTurniertagID]->getUhrzEnde();
        $derzTurniertag = $turniertage[$derzTurniertagID];
        $derzPlatz = $anfangFeld;

        $em = $this->getDoctrine()->getManager();

        foreach ($verteilungsArray as $spieltag) {

            foreach ($spieltag->getSpiele() as $spiel) {
                //Spielspezifika setzen
                $spiel->setTurniertag($derzTurniertag);
                $spiel->setUhrzeit($derzSpielzeit);
                if ($anzFelder > 1)
                    $spiel->setSpielfeld($derzPlatz % $anzFelder + 1);
                else
                    $spiel->setSpielfeld($derzPlatz+1);

                $em->persist($spiel);
                $em->flush();

                if ($anzFelder > 1)
                    $derzPlatz++;

                //Spielzeit hochzaehlen, wenn eine Runde durch ist
                if ($anzFelder == 1)
                    $derzSpielzeit->add(new \DateInterval("PT" . $spielZeit . "M"));
                else if ($derzPlatz % $anzFelder == (0+$anfangFeld))
                    $derzSpielzeit->add(new \DateInterval("PT" . $spielZeit . "M"));

                //Tag wechseln
                if ($derzSpielzeit == $endSpielzeit) {
                    $derzTurniertagID++;
                    $derzTurniertag = $turniertage[$derzTurniertagID];
                    $derzSpielzeit = $turniertage[$derzTurniertagID]->getUhrzBeginn();
                    $endSpielzeit = $turniertage[$derzTurniertagID]->getUhrzEnde();
                    $derzPlatz = 0;
                }
            }
        }

        //$this->pruefeVereinskollision();
    }

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

    /**
     * Hilfsfunktion zum Sortieren der Spieltage anhand der Staerke
     * @param $spieltag1
     * @param $spieltag2
     * @return int
     */
    private function cmpSpieltag($spieltag1, $spieltag2)
    {
        if ($spieltag1->getStaerkstesSpiel()->getStaerke() > $spieltag2->getStaerkstesSpiel()->getStaerke())
            return 1;
        return 0;
    }

    /**
     * Hilfsfunktion zum Sortieren der VerteilerWrapper anhand der Spieltage
     * @param $vt1
     * @param $vt2
     * @return int
     */
    private function cmpVerteilerWrapperSp($vt1, $vt2)
    {
        if ($vt1->getAnzSpieltage() > $vt2->getAnzSpieltage())
            return 1;
        return 0;
    }

    /**
     * Hilfsfunktion zum Sortieren der VerteilerWrapper andhand des Geschlechts
     * @param $vt1
     * @param $vt2
     * @return int
     */
    private function cmpVerteilerWrapperGe($vt1, $vt2)
    {
        if ($vt1->getGeschlecht() > $vt2->getGeschlecht())
            return 1;
        return 0;
    }
}