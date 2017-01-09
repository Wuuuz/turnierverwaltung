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
use Symfony\Component\HttpFoundation\Response;
use TeilnehmerBundle\Entity\Mannschaft;
use TeilnehmerBundle\Utils\AltersklasseAnmeldungWrapper;
use TeilnehmerBundle\Utils\AnmeldungAltersklasseWrapper;
use TeilnehmerBundle\Utils\AnmeldungMannschaftWrapper;
use TurnierplanBundle\Entity\Endrundenspiel;
use TurnierplanBundle\Entity\Hauptrundenspiel;
use TurnierplanBundle\Entity\Vorrundenspiel;

class SpielplanController extends Controller
{
    /**
     * @Route("/turnierplan", name="turnierplanList")
     */
    public function listAction()
    {
       return $this->render('TurnierplanBundle:Spielplan:list.html.twig');
    }

    /**
     * @Route("/turnierplan/spiele/api", name="turnierplanListAPI")
     */
    public function turnierplanApi()
    {
        $request = Request::createFromGlobals();

        $typ = $request->query->get('ausgabeTyp');
        if($typ == "true")
            $typ = 2;
        else
            $typ = 1;


        $spiele = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Spiel')
            ->findAll();

        $json = '{  "draw": 1,
                    "recordsTotal": '.count($spiele).',
                    "recordsFiltered": '.count($spiele).',
                    "data": [';

        for($i = 0; $i < count($spiele); $i++)
        {
            if($spiele[$i] instanceof Vorrundenspiel)
            {
                    $mannschaft1 = $spiele[$i]->getMannschaft1()->getName();
                    $mannschaft2 = $spiele[$i]->getMannschaft2()->getName();
            }
            else if($spiele[$i] instanceof Hauptrundenspiel)
            {
                if($typ == 1){
                    $mannschaft1 = $spiele[$i]->getHeimPlatz().". Gruppe ".$spiele[$i]->getHeimGruppe();
                    $mannschaft2 = $spiele[$i]->getGastPlatz().". Gruppe ".$spiele[$i]->getGastGruppe();
                }
                else if($typ == 2)
                {
                    if($spiele[$i]->getMannschaft1() == null)
                        $mannschaft1 = "Nicht berechenbar";
                    else
                        $mannschaft1 = $spiele[$i]->getMannschaft1()->getName();

                    if($spiele[$i]->getMannschaft2() == null)
                        $mannschaft2 = "Nicht berechenbar";
                    else
                        $mannschaft2 = $spiele[$i]->getMannschaft2()->getName();
                }
            }
            else if($spiele[$i] instanceof Endrundenspiel)
            {
                if($typ == 1){
                    if($spiele[$i]->getGvFlag() == 1)
                        $gv="Gewinner Spiel";
                    else
                        $gv="Verlierer Spiel";

                    $mannschaft1 = $gv." ".$spiele[$i]->getHauptrundenspiel1()->getSpielnummer();
                    $mannschaft2 = $gv." ".$spiele[$i]->getHauptrundenspiel2()->getSpielnummer();
                }
                else if($typ == 2)
                {
                    if($spiele[$i]->getMannschaft1() == null)
                        $mannschaft1 = "Nicht berechenbar";
                    else
                        $mannschaft1 = $spiele[$i]->getMannschaft1()->getName();

                    if($spiele[$i]->getMannschaft2() == null)
                        $mannschaft2 = "Nicht berechenbar";
                    else
                        $mannschaft2 = $spiele[$i]->getMannschaft2()->getName();
                }
            }

            $schiedsrichter = "";
            if($spiele[$i]->getSchiedsrichter1() != null){
                $schiedsrichter = $spiele[$i]->getSchiedsrichter1()->getName();
                if($spiele[$i]->getSchiedsrichter2() != null){
                    $schiedsrichter .= "/".$spiele[$i]->getSchiedsrichter2()->getName();
                }
            }

            $ergebnis = "";
            if($spiele[$i]->getErgHeim() != null)
                $ergebnis = $spiele[$i]->getErgHeim().":".$spiele[$i]->getErgGast();


            $json .= '{ "DT_RowId": "'.$spiele[$i]->getId().'",
                        "uhrzeit": "'.$spiele[$i]->getUhrzeit()->format('d.m.Y H:i').'",
                        "platz": "'.$spiele[$i]->getSpielfeld().'",
                        "jugend": "'.$spiele[$i]->getAltersklasse()->getBezeichnung().'",
                        "heim": "'.$mannschaft1.'",
                        "gast": "'.$mannschaft2.'",
                        "schiedsrichter": "'.$schiedsrichter.'",
                        "ergebnis": "'.$ergebnis.'"}';

            if($i+1 != count($spiele))
                $json .= ',';
        }

        return new Response($json.' ] }');
    }

}