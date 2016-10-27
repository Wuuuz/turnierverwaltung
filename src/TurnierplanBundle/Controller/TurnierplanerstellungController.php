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

class TurnierplanerstellungController extends Controller
{
    /**
     * @Route("/turnierplan/neu/gruppen")
     */
    public function gruppenEinteilenAction()
    {
        //Holt alle verfuegbaren Altersklassen
        $altersklassen = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Altersklasse')
            ->findAll();

        //Holt fuer jede Altersklasse die Mannschaften
        foreach ($altersklassen as $altersklasse) {
            $mannschaften = $this->getDoctrine()
                ->getRepository('TeilnehmerBundle:Mannschaft')
                ->findBy(array('altersklasse' => $altersklasse), array('starke' => 'DESC'));

            //Falls mehr als 7 Mannschaften -> zwei Gruppen
            if (count($mannschaften) > 7)
                $gruppenAnzahl = 2;
            else
                $gruppenAnzahl = 1;

            $em = $this->getDoctrine()->getManager();

            //Gruppeneinteilung nach Staerke
            for ($i = 0; $i < count($mannschaften); $i++) {
                $mannschaften[$i]->setGruppe($i % $gruppenAnzahl + 1);
                $em->persist($mannschaften[$i]);
            }

            // actually executes the queries (i.e. the INSERT query)
            $em->flush();
        }

        return $this->render('TurnierplanBundle:Default:index.html.twig');
    }

    /**
     * @Route("/turnierplan/neu/gruppenUeberpruefen")
     */
    public function gruppenUeberpreufenAction(){

    }

    /**
     * @Route("/turnierplan/neu/spieleErzeugen")
     */
    public function spieleErzeugenAction(){
        $anzRunden = 1;
        $typ = 4;

        $altersklassen = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Altersklasse')
            ->findAll();

        foreach($altersklassen as $altersklasse) {
            $anzMann = count($this->getDoctrine()
                ->getRepository('TeilnehmerBundle:Mannschaft')
                ->findBy(array('altersklasse' => $altersklasse)));

            //Vorrundenspiele erzeugen
            for($i = 0; $i < $anzRunden; $i++) {
                $this->erzeugeVorrundenspiele($altersklasse, 1);
                if ($anzMann > 7)
                    $this->erzeugeVorrundenspiele($altersklasse, 2);
            }

            //Platzierungsspiele erzeugen
            $this->erzeugePlatzierungsspiele($altersklasse, $typ);
        }

        return $this->render('TurnierplanBundle:Default:index.html.twig');
    }

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

        //Turniertage holen und Anzahl der möglichen Minuten berechnen
        $turniertage = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Turniertag')
            ->findBy(array(),array('uhrzBeginn' => 'ASC'));

        foreach($turniertage as $turniertag)
            $gesamtZeit += abs($turniertag->getUhrzEnde()->getTimestamp() - $turniertag->getUhrzBeginn()->getTimestamp())/60;

        $derzTurniertagID = 0;
        $derzSpielzeit = $turniertage[$derzTurniertagID]->getUhrzBeginn();
        $endSpielzeit = $turniertage[$derzTurniertagID]->getUhrzEnde();
        $derzTurniertag = $turniertage[$derzTurniertagID];
        $derzPlatz = 0;

        //Spielen Uhrzeit, Platz und Turniertag zuweisen
        if($typ == 1) {
            $spielZeit = $anzSp*$gesamtZeit/$gesamtSpiele;

            $em = $this->getDoctrine()->getManager();

            foreach ($verteilungsArray as $spieltag) {

                foreach ($spieltag->getSpiele() as $spiel) {
                    //Spielspezifika setzen
                    $spiel->setTurniertag($derzTurniertag);
                    $spiel->setUhrzeit($derzSpielzeit);
                    $spiel->setSpielfeld($derzPlatz % $anzSp + 1);

                    $em->persist($spiel);
                    $em->flush();

                    $derzPlatz++;

                    //Spielzeit hochzaehlen, wenn eine Runde durch ist
                    if ($derzPlatz % $anzSp == 0) {
                        $derzSpielzeit->add(new \DateInterval("PT" . $spielZeit . "M"));
                    }
                    //Tag wechseln
                    if ($derzSpielzeit == $endSpielzeit) {

                        $derzTurniertagID = 1;
                        $derzTurniertag = $turniertage[$derzTurniertagID];
                        $derzSpielzeit = $turniertage[$derzTurniertagID]->getUhrzBeginn();
                        $endSpielzeit = $turniertage[$derzTurniertagID]->getUhrzEnde();
                        $derzPlatz = 0;
                    }
                }
            }
        }
        if($typ == 2)
        {
            $meistenSpiele = 0;

            for($i = 0; $i < count($verteilungsArray); $i++){
                $meistenSpieleTemp = 0;

                foreach($verteilungsArray[$i] as $spieltagWrapper)
                    $meistenSpieleTemp += $spieltagWrapper->getAnzSpiele();

                if($meistenSpieleTemp > $meistenSpiele)
                    $meistenSpiele = $meistenSpieleTemp;
            }

            $spielZeit = $gesamtZeit/$meistenSpiele;


            for($i = 0; $i < count($verteilungsArray); $i++)
            {
                foreach($verteilungsArray[$i] as $spieltag)
                {
                    foreach($spieltag as $spiel)
                    {
                        //Spielspezifika setzen
                        $spiel->setTurniertag($derzTurniertag);
                        $spiel->setUhrzeit($derzSpielzeit);
                        $spiel->setSpielfeld($i + 1);

                        //Genaue Implementierung noch anschauen!
//                        $em->persist($spiel);
//                        $em->flush();
//
//                        $derzPlatz++;
//
//                        //Spielzeit hochzaehlen, wenn eine Runde durch ist
//                        $derzSpielzeit->add(new \DateInterval("PT" . $spielZeit . "M"));
//
//                        //Tag wechseln
//                        if ($derzSpielzeit == $endSpielzeit) {
//
//                            $derzTurniertagID = 1;
//                            $derzTurniertag = $turniertage[$derzTurniertagID];
//                            $derzSpielzeit = $turniertage[$derzTurniertagID]->getUhrzBeginn();
//                            $endSpielzeit = $turniertage[$derzTurniertagID]->getUhrzEnde();
//                            $derzPlatz = 0;
//                        }
                    }
                }
            }
        }

        return $this->render('TurnierplanBundle:Default:index.html.twig');
    }

    /**
     * Erzeugt fuer eine Altersklasse und eine Gruppe jeweils eine Vorrunde
     * @param $altersklasse
     * @param $gruppe
     */
    public function erzeugeVorrundenspiele($altersklasse, $gruppe)
    {
        //Holt Mannschaften für eine Altersklasse und Gruppe
        $mannschaften = $this->getDoctrine()
            ->getRepository('TeilnehmerBundle:Mannschaft')
            ->findBy(array('altersklasse' => $altersklasse, 'gruppe' => $gruppe));

        // Testen ob die Anzahl der Teams gerade ist, wenn nicht das Team "frei" hinzufuegen
        if (count($mannschaften) % 2) {
            $leereMannschaft = new Mannschaft();
            $leereMannschaft->setGruppe(-1);
            array_push($mannschaften, $leereMannschaft);
        }

        //Erzeugt Spieltage mit Vorrundenspiele
        $spielTag = array();
        $anz = count($mannschaften);        // Anzahl der Teams im Array $teams
        $paare = $anz / 2;                    // Anzahl der möglichen Spielpaare
        $tage = $anz - 1;                    // Anzahl der Spieltage pro Runde
        $spiele = $paare * $tage;            // Anzahl der Spiele pro Hin-/Rück-Runde
        $xpos = $anz - 1;                    // höchster Key im Array $teams
        $spnr = 0;                        // Zähler für Spielnummer
        $sppaar = 0;                        // Zähler für Spielpaar
        $i = 0;                                // Schleifenzähler

        for ($tag = 1; $tag < $anz; $tag++) {
            array_splice($mannschaften, 1, 1, array(array_pop($mannschaften), $mannschaften[1]));
            $spielTag[$tag - 1] = new Spieltag();

            for ($sppaar = 0; $sppaar < $paare; $sppaar++) {
                $spnr++;

                // wechseln zwischen G und H-Spiel:
                if (($spnr % $anz != 1) and ($sppaar % 2 == 0)) {
                    $hteam = $mannschaften[$sppaar];
                    $gteam = $mannschaften[$xpos - $sppaar];
                } else {
                    $gteam = $mannschaften[$sppaar];
                    $hteam = $mannschaften[$xpos - $sppaar];
                }

                if ($gteam->getGruppe() != "-1" && $hteam->getGruppe() != "-1") {
                    $vorrundenspiel = new Vorrundenspiel();
                    $vorrundenspiel->setMannschaft1($hteam);
                    $vorrundenspiel->setMannschaft2($gteam);
                    $vorrundenspiel->setAltersklasse($altersklasse);
                    $spielTag[$tag - 1]->addSpiel($vorrundenspiel);
                }
            }
        }

        //Sortiert nach stärksten Spielen
        usort($spielTag, array($this, "cmpSpieltag"));

        //Speichert Spiele ab
        $em = $this->getDoctrine()->getManager();
        for ($i = 0; $i < count($spielTag); $i++) {
            $spieltagSpiele = $spielTag[$i]->spiele;

            for ($j = 0; $j < count($spieltagSpiele); $j++) {
                $em->persist($spieltagSpiele[$j]);
            }
        }

        $em->flush();

    }

    /**
     * Erzeugt fuer eine Alterklasse alle Platzierungsspiele
     * @param $altersklasse
     * @param $type Art der Platzierungsspiele
     */
    public function erzeugePlatzierungsspiele($altersklasse, $typ)
    {
        $mannschaften = $this->getDoctrine()
            ->getRepository('TeilnehmerBundle:Mannschaft')
            ->findBy(array('altersklasse' => $altersklasse));

        $em = $this->getDoctrine()->getManager();


        if ($typ == 1) {
            $altersklasse->setSpielmodus(1);

            for ($i = floor(count($mannschaften)); $i >= 0; $i--) {
                $hauptRundenSpiel = new Hauptrundenspiel($i + 1, 1, $i + 1, 2);
                $hauptRundenSpiel->setAltersklasse($altersklasse);
                $hauptRundenSpiel->setPlatzUm($i * 2 + 1);

                $em->persist($hauptRundenSpiel);
            }
        } //1-1, 2-2, 3-3, ...

        else if ($typ == 2) {
            $altersklasse->setSpielmodus(2);
            $spielUm = 1;
            for ($i = 0; $i < floor(count($mannschaften) / 4); $i++) {
                $hauptRundenSpiel1 = new Hauptrundenspiel($i * 2 + 1, 1, $i * 2 + 2, 2);
                $hauptRundenSpiel1->setAltersklasse($altersklasse);
                $hauptRundenSpiel1->setPlatzUm($i*2+100);
                $em->persist($hauptRundenSpiel1);

                $hauptRundenSpiel2 = new Hauptrundenspiel($i * 2 + 2, 1, $i * 2 + 1, 2);
                $hauptRundenSpiel2->setAltersklasse($altersklasse);
                $hauptRundenSpiel2->setPlatzUm($i*2+1+100);
                $em->persist($hauptRundenSpiel2);

                $endRundenSpiel1 = new Endrundenspiel(0, $hauptRundenSpiel1, $hauptRundenSpiel2);
                $endRundenSpiel1->setAltersklasse($altersklasse);
                $endRundenSpiel1->setPlatzUm($spielUm + 2);
                $em->persist($endRundenSpiel1);

                $endRundenSpiel2 = new Endrundenspiel(1, $hauptRundenSpiel1, $hauptRundenSpiel2);
                $endRundenSpiel2->setAltersklasse($altersklasse);
                $endRundenSpiel2->setPlatzUm($spielUm);
                $em->persist($endRundenSpiel2);

                $spielUm += 4;
            }
            //wenn zwei Mannschaften übrig bleiben -> Hin- und Rueckspiel
            if (count($mannschaften) % 4 == 2) {
                $hauptRundenSpiel = new Hauptrundenspiel(count($mannschaften) / 2, 1, count($mannschaften) / 2, 2);
                $hauptRundenSpiel->setAltersklasse($altersklasse);
                $hauptRundenSpiel->setPlatzUm(count($mannschaften) - 1);
                $em->persist($hauptRundenSpiel);

                $hauptRundenSpiel = new Hauptrundenspiel(count($mannschaften) / 2, 2, count($mannschaften) / 2, 1);
                $hauptRundenSpiel->setAltersklasse($altersklasse);
                $hauptRundenSpiel->setPlatzUm(count($mannschaften) - 1);
                $em->persist($hauptRundenSpiel);
            } else if (count($mannschaften) % 4 == 3) {
                $hauptRundenSpiel3 = new Hauptrundenspiel(floor(count($mannschaften) / 2), 1, floor(count($mannschaften) / 2), 2);
                $hauptRundenSpiel3->setAltersklasse($altersklasse);
                $hauptRundenSpiel3->setPlatzUm(count($mannschaften) - 2);
                $em->persist($hauptRundenSpiel3);

                $hauptRundenSpiel4 = new Hauptrundenspiel(floor(count($mannschaften) / 2) + 1, 1, floor(count($mannschaften) / 2), 2);
                $hauptRundenSpiel4->setAltersklasse($altersklasse);
                $hauptRundenSpiel4->setPlatzUm(count($mannschaften) - 2);
                $em->persist($hauptRundenSpiel4);

                $hauptRundenSpiel5 = new Hauptrundenspiel(floor(count($mannschaften) / 2), 2, floor(count($mannschaften) / 2) + 1, 1);
                $hauptRundenSpiel5->setAltersklasse($altersklasse);
                $hauptRundenSpiel5->setPlatzUm(count($mannschaften) - 2);
                $em->persist($hauptRundenSpiel5);
            }
        }  //1-2, 2-1, 3-4, 4-3...

        else if ($typ == 3) {
            $altersklasse->setSpielmodus(3);

            for ($i = 0; $i < floor(count($mannschaften) / 4); $i++) {
                $hauptRundenSpiel1 = new Hauptrundenspiel(($i + 1) * 4 - 3, 1, ($i + 1) * 4, 1);
                $hauptRundenSpiel1->setAltersklasse($altersklasse);
                $hauptRundenSpiel1->setPlatzUm($i*2+100);
                $em->persist($hauptRundenSpiel1);

                $hauptRundenSpiel2 = new Hauptrundenspiel(($i + 1) * 4 - 2, 1, ($i + 1) * 4 - 1, 1);
                $hauptRundenSpiel2->setAltersklasse($altersklasse);
                $hauptRundenSpiel1->setPlatzUm($i*2+100);
                $em->persist($hauptRundenSpiel2);

                $endRundenSpiel1 = new Endrundenspiel(0, $hauptRundenSpiel1, $hauptRundenSpiel2);
                $endRundenSpiel1->setAltersklasse($altersklasse);
                $endRundenSpiel1->setPlatzUm(($i + 1) * 4 - 1);
                $em->persist($endRundenSpiel1);

                $endRundenSpiel2 = new Endrundenspiel(1, $hauptRundenSpiel1, $hauptRundenSpiel2);
                $endRundenSpiel2->setAltersklasse($altersklasse);
                $endRundenSpiel2->setPlatzUm(($i + 1) * 4 - 3);
                $em->persist($endRundenSpiel2);
            }

            if (count($mannschaften) % 4 == 2) {
                $hauptRundenSpiel = new Hauptrundenspiel(count($mannschaften) - 1, 1, count($mannschaften), 1);
                $hauptRundenSpiel->setAltersklasse($altersklasse);
                $hauptRundenSpiel->setPlatzUm(count($mannschaften) - 1);
                $em->persist($hauptRundenSpiel);

                $hauptRundenSpiel = new Hauptrundenspiel(count($mannschaften), 1, count($mannschaften) - 1, 1);
                $hauptRundenSpiel->setAltersklasse($altersklasse);
                $hauptRundenSpiel->setPlatzUm(count($mannschaften) - 1);
                $em->persist($hauptRundenSpiel);
            } else if (count($mannschaften) % 4 == 3) {
                $hauptRundenSpiel3 = new Hauptrundenspiel(count($mannschaften) - 2, 1, count($mannschaften), 1);
                $hauptRundenSpiel3->setAltersklasse($altersklasse);
                $hauptRundenSpiel3->setPlatzUm(count($mannschaften) - 2);
                $em->persist($hauptRundenSpiel3);

                $hauptRundenSpiel4 = new Hauptrundenspiel(count($mannschaften)-1, 1, count($mannschaften), 1);
                $hauptRundenSpiel4->setAltersklasse($altersklasse);
                $hauptRundenSpiel4->setPlatzUm(count($mannschaften) - 2);
                $em->persist($hauptRundenSpiel4);

                $hauptRundenSpiel5 = new Hauptrundenspiel(count($mannschaften) - 2, 1, count($mannschaften)-1, 1);
                $hauptRundenSpiel5->setAltersklasse($altersklasse);
                $hauptRundenSpiel5->setPlatzUm(count($mannschaften) - 2);
                $em->persist($hauptRundenSpiel5);
            }
        }  //1-4, 2-3

        else if ($typ == 4) {
            $altersklasse->setSpielmodus(4);

            for ($i = 0; $i < floor(count($mannschaften)/2); $i++) {
                $hauptRundenSpiel = new Hauptrundenspiel($i * 2 + 1, 1, $i * 2 + 2, 1);
                $hauptRundenSpiel->setAltersklasse($altersklasse);
                $hauptRundenSpiel->setPlatzUm($i * 2 + 1);
                $em->persist($hauptRundenSpiel);
            }
        } //1-2,3-4

        else if ($typ == 5) {
            $altersklasse->setSpielmodus(5);
        } //nur Vorrunde

        $em->flush();
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