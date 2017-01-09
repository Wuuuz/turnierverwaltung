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
use Symfony\Component\HttpFoundation\Response;
use TeilnehmerBundle\Entity\Mannschaft;
use TeilnehmerBundle\Utils\AltersklasseAnmeldungWrapper;
use TeilnehmerBundle\Utils\AnmeldungAltersklasseWrapper;
use TeilnehmerBundle\Utils\AnmeldungMannschaftWrapper;
use TurnierplanBundle\Entity\Endrundenspiel;
use TurnierplanBundle\Entity\Hauptrundenspiel;
use TurnierplanBundle\Utils\MannschaftTabelle;

class SpieleBerechnenController extends Controller
{

    public function listAction()
    {
    }


    /**
     * @Route("/turnierplan/berechnen/spiele", name="turnierplanBerechnenSpiele")
     */
    public function spieleBerechnen()
    {
        $tabellen = $this->tabellenBerechnen();

        $hauptrundenspiele = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Hauptrundenspiel')
            ->findAll();

        $em = $this->getDoctrine()->getManager();

        foreach($hauptrundenspiele as $hauptrundenspiel)
        {
            $alterklasseID = $hauptrundenspiel->getAltersklasse()->getID();

            $mannschaft1 = $tabellen[$alterklasseID][$hauptrundenspiel->getHeimGruppe()][$hauptrundenspiel->getHeimPlatz()-1]->getMannschaft();
            $mannschaft2 = $tabellen[$alterklasseID][$hauptrundenspiel->getGastGruppe()][$hauptrundenspiel->getGastPlatz()-1]->getMannschaft();

            $hauptrundenspiel->setMannschaft1($mannschaft1);
            $hauptrundenspiel->setMannschaft2($mannschaft2);

            $em->persist($hauptrundenspiel);
        }

        $endrundenspiele = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Endrundenspiel')
            ->findAll();

        foreach($endrundenspiele as $endrundenspiel)
        {
            if($endrundenspiel->getGvFlag() == 1) {
                $spiel1 = $this->getDoctrine()
                    ->getRepository('TurnierplanBundle:Hauptrundenspiel')
                    ->findOneBy(array('id' => $endrundenspiel->getHauptrundenspiel1()->getId()));

                if ($spiel1->getErgGast() != null && $spiel1->getErgGast() != null) {
                    if ($spiel1->getErgGast() > $spiel1->getErgHeim())
                        $endrundenspiel->setMannschaft1($spiel1->getMannschaft2());
                    else
                        $endrundenspiel->setMannschaft1($spiel1->getMannschaft1());
                }

                $spiel2 = $this->getDoctrine()
                    ->getRepository('TurnierplanBundle:Hauptrundenspiel')
                    ->findOneBy(array('id' => $endrundenspiel->getHauptrundenspiel2()->getId()));

                if ($spiel2->getErgGast() != null && $spiel2->getErgGast() != null) {
                    if ($spiel2->getErgGast() > $spiel2->getErgHeim())
                        $endrundenspiel->setMannschaft2($spiel2->getMannschaft2());
                    else
                        $endrundenspiel->setMannschaft2($spiel2->getMannschaft1());
                }
            }
            else {
                $spiel1 = $this->getDoctrine()
                    ->getRepository('TurnierplanBundle:Hauptrundenspiel')
                    ->findOneBy(array('id' => $endrundenspiel->getHauptrundenspiel1()->getId()));

                if ($spiel1->getErgGast() != null && $spiel1->getErgGast() != null) {
                    if ($spiel1->getErgGast() > $spiel1->getErgHeim())
                        $endrundenspiel->setMannschaft1($spiel1->getMannschaft1());
                    else
                        $endrundenspiel->setMannschaft1($spiel1->getMannschaft2());
                }

                $spiel2 = $this->getDoctrine()
                    ->getRepository('TurnierplanBundle:Hauptrundenspiel')
                    ->findOneBy(array('id' => $endrundenspiel->getHauptrundenspiel2()->getId()));

                if ($spiel2->getErgGast() != null && $spiel2->getErgGast() != null) {
                    if ($spiel2->getErgGast() > $spiel2->getErgHeim())
                        $endrundenspiel->setMannschaft2($spiel2->getMannschaft1());
                    else
                        $endrundenspiel->setMannschaft2($spiel2->getMannschaft2());
                }

                $em->persist($endrundenspiel);
            }
        }

        $em->flush();

        return $this->render('TurnierplanBundle:Spielplan:list.html.twig');
    }

    private function tabellenBerechnen()
    {
        $alterklassenTabellen = array();

        $altersklassen = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Altersklasse')
            ->findAll();

        foreach($altersklassen as $altersklasse)
        {
            $mannschaften = $this->getDoctrine()
                ->getRepository('TeilnehmerBundle:Mannschaft')
                ->findAll();

            if(count($mannschaften) > 7) // zwei Gruppen
            {

            }
            else if(count($mannschaften) > 0) // eine Gruppe
            {
                $mannschaftsArray = array();

                for($i = 0; $i < count($mannschaften); $i++)
                    $mannschaftsArray[$mannschaften[$i]->getId()] = new MannschaftTabelle($mannschaften[$i]);

                $spiele = $this->getDoctrine()
                    ->getRepository('TurnierplanBundle:Vorrundenspiel')
                    ->findGespielteSpiele($altersklasse);

                foreach ($spiele as $spiel)
                {
                    $mannschaftsArray[$spiel->getMannschaft1()->getID()]->erhoeheSpiele();
                    $mannschaftsArray[$spiel->getMannschaft2()->getID()]->erhoeheSpiele();

                    $mannschaftsArray[$spiel->getMannschaft1()->getID()]->erhoeheTore($spiel->getErgGast(),$spiel->getErgHeim());
                    $mannschaftsArray[$spiel->getMannschaft2()->getID()]->erhoeheTore($spiel->getErgHeim(),$spiel->getErgGast());

                    if($spiel->getErgHeim() > $spiel->getErgGast())
                    {
                        $mannschaftsArray[$spiel->getMannschaft1()->getID()]->schreibeGewinn();
                        $mannschaftsArray[$spiel->getMannschaft2()->getID()]->schreibeNiederlage();
                    }
                    else if ($spiel->getErgHeim() < $spiel->getErgGast())
                    {
                        $mannschaftsArray[$spiel->getMannschaft2()->getID()]->schreibeGewinn();
                        $mannschaftsArray[$spiel->getMannschaft1()->getID()]->schreibeNiederlage();
                    }
                    else
                    {
                        $mannschaftsArray[$spiel->getMannschaft1()->getID()]->schreibeUnentschieden();
                        $mannschaftsArray[$spiel->getMannschaft2()->getID()]->schreibeUnentschieden();
                    }
                }
            }

            array_values($mannschaftsArray);
            usort($mannschaftsArray,array($this, "cmpMannschaftTabelle"));
            $alterklassenTabellen[$altersklasse->getId()][1] = $mannschaftsArray;
        }

        return $alterklassenTabellen;
    }

    private function cmpMannschaftTabelle($mannschaft1, $mannschaft2)
    {

        if ($mannschaft1->getPunktePlus() > $mannschaft2->getPunktePlus())
            return 1;
        elseif ($mannschaft1->getPunktePlus() == $mannschaft2->getPunktePlus())
        {
            if ($mannschaft1->getTorePlus() > $mannschaft2->getTorePlus())
                return 1;
        }
        return 0;
    }

}