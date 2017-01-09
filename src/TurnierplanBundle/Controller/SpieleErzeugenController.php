<?php
/**
 * Created by PhpStorm.
 * User: Andreas
 * Date: 06.12.2016
 * Time: 13:34
 */

namespace TurnierplanBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use TeilnehmerBundle\Entity\Mannschaft;
use TeilnehmerBundle\Utils\AltersklasseAnmeldungWrapper;
use TurnierplanBundle\Entity\Endrundenspiel;
use TurnierplanBundle\Entity\Hauptrundenspiel;
use TurnierplanBundle\Entity\Vorrundenspiel;
use TurnierplanBundle\Utils\Spieltag;
use TurnierplanBundle\Utils\SpieltagWrapper;

class SpieleErzeugenController extends Controller
{
    /**
     * @Route("/turnierplan/erstellung/3", name="spErzeugenList")
     */
    public function listAction()
    {
        $turniertage = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Turniertag')
            ->findAll();

        return $this->render('TurnierplanBundle:Turnierspezifika:list.html.twig', array(
            'turniertage' => $turniertage
        ));
    }

    /**
     * @Route("/turnierplan/erstellung/3/edit", name="spErzeugen")
     */
    public function spieleErzeugenAction(){

        $request = Request::createFromGlobals();

        $altersklassen = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Altersklasse')
            ->findAll();

        foreach($altersklassen as $altersklasse) {

            $anzMann = count($this->getDoctrine()
                ->getRepository('TeilnehmerBundle:Mannschaft')
                ->findBy(array('altersklasse' => $altersklasse)));

            if($anzMann != 0) {
                $altersklasseInfo = $request->request->get($altersklasse->getId());

                $anzRunden = $altersklasseInfo[0];
                $typ = $altersklasseInfo[1];

                //Vorrundenspiele erzeugen Gruppe 1
                for ($i = 0; $i < $anzRunden; $i++) {
                    $this->erzeugeVorrundenspiele($altersklasse, 1);
                }

                //Falls vorhanden, Vorrundenspiele Gruppe 2
                if ($anzMann > 7) {
                    $anzRunden2 = $altersklasseInfo[2];
                    for ($i = 0; $i < $anzRunden2; $i++)
                        $this->erzeugeVorrundenspiele($altersklasse, 2);
                }

                //Platzierungsspiele erzeugen
                $this->erzeugePlatzierungsspiele($altersklasse, $typ);
            }
        }

        return $this->redirectToRoute('spErzeugenList');
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
}