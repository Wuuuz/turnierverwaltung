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
use TeilnehmerBundle\Entity\Mannschaft;
use TeilnehmerBundle\Utils\AltersklasseAnmeldungWrapper;
use TurnierplanBundle\Entity\Endrundenspiel;
use TurnierplanBundle\Entity\Hauptrundenspiel;
use TurnierplanBundle\Entity\Vorrundenspiel;

class SchiedsrichtereinteilungController extends Controller
{
    /**
     * @Route("/turnierplan/schiedsrichtereinteilung", name="turnierplanSrList")
     */
    public function listAction()
    {
        return $this->render('TurnierplanBundle:Schiedsrichtereinteilung:list.html.twig');
    }

    /**
     * @Route("/turnierplan/schiedsrichtereinteilung/edit", name="turnierplanSrEdit")
     * @Method({"POST"})
     */
    public function editAction()
    {
        //$this->denyAccessUnlessGranted('ROLE_TURNIERPLAN_SR_EDIT');

        $request = Request::createFromGlobals();

        $spiel = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Spiel')
            ->findOneBy(array('id' => $request->request->get('rowId')));

        if($request->request->get('schiedsrichterId1') != 0)
        {
            $sr = $this->getDoctrine()
                ->getRepository('TeilnehmerBundle:Schiedsrichter')
                ->findOneBy(array('id' => $request->request->get('schiedsrichterId1')));

            if(count($sr) == 0)
                return new Response('Schiedsrichter not valid',Response::HTTP_NOT_FOUND);

            $spiel->setSchiedsrichter1($sr);
        }
        if($request->request->get('schiedsrichterId2') != 0)
        {
            $sr = $this->getDoctrine()
                ->getRepository('TeilnehmerBundle:Schiedsrichter')
                ->findOneBy(array('id' => $request->request->get('schiedsrichterId2')));

            if(count($sr) == 0)
                return new Response('Schiedsrichter not valid',Response::HTTP_NOT_FOUND);

            $spiel->setSchiedsrichter2($sr);
        }

        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($spiel);
        $em->flush();

        return new Response();
    }

    /**
     * @Route("/turnierplan/schiedsrichtereinteilung/api", name="turnierplanSrAPI")
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

        $sr = $this->getDoctrine()
            ->getRepository('TeilnehmerBundle:Schiedsrichter')
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

                $sr1 = $sr2 = '<select class=\"selectpicker\" data-live-search=\"true\" title=\"Schiedsrichter1\"><optgroup label=\"verfuegbare Schiedsrichter\">';
                foreach ($sr as $sr_einzeln) {
                    if ($spiele[$i]->getSchiedsrichter1() == $sr_einzeln) {
                        $sr1 .= '<option data-subtext=\"' . $sr_einzeln->getVerein()->getName() . '\" value=\"' . $sr_einzeln->getId() . '\" selected>' . $sr_einzeln->getName() . '</option>';
                    } else
                        $sr1 .= '<option data-subtext=\"' . $sr_einzeln->getVerein()->getName() . '\" value=\"' . $sr_einzeln->getId() . '\">' . $sr_einzeln->getName() . '</option>';

                    if ($spiele[$i]->getSchiedsrichter2() == $sr_einzeln)
                        $sr2 .= '<option data-subtext=\"' . $sr_einzeln->getVerein()->getName() . '\" value=\"' . $sr_einzeln->getId() . '\" selected>' . $sr_einzeln->getName() . '</option>';
                    else
                        $sr2 .= '<option data-subtext=\"' . $sr_einzeln->getVerein()->getName() . '\" value=\"' . $sr_einzeln->getId() . '\">' . $sr_einzeln->getName() . '</option>';
                }

                $sr1 .= '</optgroup></select>';
                $sr2 .= '</optgroup></select>';

                $json .= '{ "DT_RowId": "' . $spiele[$i]->getId() . '",
                        "uhrzeit": "' . $spiele[$i]->getUhrzeit()->format('d.m.Y H:i') . '",
                        "platz": "' . $spiele[$i]->getSpielfeld() . '",
                        "jugend": "' . $spiele[$i]->getAltersklasse()->getBezeichnung() . '",
                        "heim": "' . $mannschaft1 . '",
                        "gast": "' . $mannschaft2 . '",
                        "sr1": "' . $sr1 . '",
                        "sr2": "' . $sr2 . '"}';

                if ($i + 1 != count($spiele))
                    $json .= ',';
            }
        }

        return new Response($json.' ] }');
    }

}