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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TurnierplanBundle\Entity\Endrundenspiel;
use TurnierplanBundle\Entity\Hauptrundenspiel;
use TurnierplanBundle\Entity\Vorrundenspiel;

class ErgebniserfassungController extends Controller
{
    /**
     * @Route("/turnierplan/ergebniserfassung", name="turnierplanErgList")
     */
    public function listAction()
    {
        return $this->render('TurnierplanBundle:Ergebniserfassung:list.html.twig');
    }


    /**
     * @Route("/turnierplan/ergebniserfassung/api", name="turnierplanErgAPI")
     */
    public function turnierplanErgApi()
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
            if($spiele[$i]->getAltersklasse()->getBezeichnung() != "FS") {
                if ($spiele[$i] instanceof Vorrundenspiel) {
                    $mannschaft1 = $spiele[$i]->getMannschaft1()->getName();
                    $mannschaft2 = $spiele[$i]->getMannschaft2()->getName();
                } else if ($spiele[$i] instanceof Hauptrundenspiel) {
                    if ($typ == 1) {
                        $mannschaft1 = $spiele[$i]->getHeimPlatz() . ". Gruppe " . $spiele[$i]->getHeimGruppe();
                        $mannschaft2 = $spiele[$i]->getGastPlatz() . ". Gruppe " . $spiele[$i]->getGastGruppe();
                    } else if ($typ == 2) {
                        if ($spiele[$i]->getMannschaft1() == null)
                            $mannschaft1 = "Nicht berechenbar";
                        else
                            $mannschaft1 = $spiele[$i]->getMannschaft1()->getName();

                        if ($spiele[$i]->getMannschaft2() == null)
                            $mannschaft2 = "Nicht berechenbar";
                        else
                            $mannschaft2 = $spiele[$i]->getMannschaft2()->getName();
                    }
                } else if ($spiele[$i] instanceof Endrundenspiel) {
                    if ($typ == 1) {
                        if ($spiele[$i]->getGvFlag() == 1)
                            $gv = "Gewinner Spiel";
                        else
                            $gv = "Verlierer Spiel";

                        $mannschaft1 = $gv . " " . $spiele[$i]->getHauptrundenspiel1()->getSpielnummer();
                        $mannschaft2 = $gv . " " . $spiele[$i]->getHauptrundenspiel2()->getSpielnummer();
                    } else if ($typ == 2) {
                        if ($spiele[$i]->getMannschaft1() == null)
                            $mannschaft1 = "Nicht berechenbar";
                        else
                            $mannschaft1 = $spiele[$i]->getMannschaft1()->getName();

                        if ($spiele[$i]->getMannschaft2() == null)
                            $mannschaft2 = "Nicht berechenbar";
                        else
                            $mannschaft2 = $spiele[$i]->getMannschaft2()->getName();
                    }
                }

                $ergHeim = "";
                $ergGast = "";
                if ($spiele[$i]->getErgHeim() != null)
                    $ergHeim = $spiele[$i]->getErgHeim();
                if ($spiele[$i]->getErgGast() != null)
                    $ergGast = $spiele[$i]->getErgGast();


                $json .= '{ "DT_RowId": "' . $spiele[$i]->getId() . '",
                        "uhrzeit": "' . $spiele[$i]->getUhrzeit()->format('d.m.Y H:i') . '",
                        "platz": "' . $spiele[$i]->getSpielfeld() . '",
                        "jugend": "' . $spiele[$i]->getAltersklasse()->getBezeichnung() . '",
                        "heim": "' . $mannschaft1 . '",
                        "gast": "' . $mannschaft2 . '",
                        "ergHeim": "<input type=\"text\" size=\"2\" class=\"inputErg\" value=\"' . $ergHeim . '\">",
                        "ergGast": "<input type=\"text\" size=\"2\" class=\"inputErg\" value=\"' . $ergGast . '\">"}';

                if ($i + 1 != count($spiele))
                    $json .= ',';
            }
        }

        return new Response($json.' ] }');
    }

    /**
     * @Route("/turnierplan/ergebniserfassung/edit", name="turnierplanErgEdit")
     * @Method({"POST"})
     */
    public function editAction()
    {
        //$this->denyAccessUnlessGranted('ROLE_TURNIERPLAN_ERG_EDIT');

        $request = Request::createFromGlobals();

        $spiel = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Spiel')
            ->findOneBy(array('id' => $request->request->get('spielId')));

        $spiel->setErgHeim($request->request->get('ergHeim'));
        $spiel->setErgGast($request->request->get('ergGast'));

        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($spiel);
        $em->flush();

        return new Response();
    }

}