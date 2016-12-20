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
use TeilnehmerBundle\Entity\Mannschaft;
use TeilnehmerBundle\Utils\AltersklasseAnmeldungWrapper;
use TeilnehmerBundle\Utils\AnmeldungAltersklasseWrapper;
use TeilnehmerBundle\Utils\AnmeldungMannschaftWrapper;
use TurnierplanBundle\Entity\Endrundenspiel;
use TurnierplanBundle\Entity\Hauptrundenspiel;

class SpielplanController extends Controller
{
    /**
     * @Route("/turnierplan", name="turnierplanList")
     */
    public function listAction()
    {
        $spiele = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Spiel')
            ->findAll();

        foreach($spiele as $spiel){

            if($spiel instanceof Hauptrundenspiel)
            {
                $mannschaft1new = new Mannschaft();
                $mannschaft2new = new Mannschaft();
                $mannschaft1new->setName($spiel->getHeimPlatz().". Gruppe ".$spiel->getHeimGruppe());
                $mannschaft2new->setName($spiel->getGastPlatz().". Gruppe ".$spiel->getGastGruppe());


                $spiel->setMannschaft1($mannschaft1new);
                $spiel->setMannschaft2($mannschaft2new);
            }
            else if($spiel instanceof Endrundenspiel)
            {
                $mannschaft1new = new Mannschaft();
                $mannschaft2new = new Mannschaft();

                if($spiel->getGvFlag() == 1)
                    $gv="Gewinner Spiel";
                else
                    $gv="Verlierer Spiel";

                $mannschaft1new->setName($gv." ".$spiel->getHauptrundenspiel1()->getSpielnummer());
                $mannschaft2new->setName($gv." ".$spiel->getHauptrundenspiel2()->getSpielnummer());

                $spiel->setMannschaft1($mannschaft1new);
                $spiel->setMannschaft2($mannschaft2new);
            }
        }

        return $this->render('TurnierplanBundle:Spielplan:list.html.twig', array(
            'spiele' => $spiele
        ));
    }

}