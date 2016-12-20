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
use TurnierplanBundle\Entity\Endrundenspiel;
use TurnierplanBundle\Entity\Hauptrundenspiel;

class SchiedsrichtereinteilungController extends Controller
{
    /**
     * @Route("/turnierplan/schiedsrichtereinteilung", name="turnierplanSrList")
     */
    public function listAction()
    {
        $spiele = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Spiel')
            ->findAll();

        $sr = $this->getDoctrine()
            ->getRepository('TeilnehmerBundle:Schiedsrichter')
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
            dump($spiel->getId());
        }

        dump($spiele);

        return $this->render('TurnierplanBundle:Schiedsrichtereinteilung:list.html.twig', array(
            'spiele' => $spiele,
            'schiedsrichter' => $sr
        ));
    }

}