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
use TeilnehmerBundle\Utils\AltersklasseAnmeldungWrapper;
use TurnierplanBundle\Entity\Turniertag;

class TurnierspezifikaController extends Controller
{
    /**
     * @Route("/turnierplan/erstellung/3", name="spTurnierspezifika")
     */
    public function listAction()
    {

        return $this->render('TurnierplanBundle:Turnierspezifika:list.html.twig');
    }

    /**
     * @Route("/turnierplan/erstellung/3/edit", name="spTurnierspezifikaEdit")
     */
    public function editAction()
    {

        $request = Request::createFromGlobals();

        $spielplan = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Spielplan')
            ->findOneBy(array('id' => 1));

        $spielplan
            ->setAnzSpFeld($request->request->get('input_spielfelder'))
            ->setSpielzeit($request->request->get('input_spielfelder'))
            ->setSpielfeldBelegung($request->request->get('input_spielfelder'));

        return $this->redirectToRoute('spErzeugen');
    }


    /**
     * @Route("/turnierplan/erstellung/turniertag/api/delete", name="spTurniertagAPIDelete")
     */
    public function turniertagAendernAPIDeleteAction()
    {
        //$this->denyAccessUnlessGranted('ROLE_TURNIERPLAN_ERSTELLEN');

        $request = Request::createFromGlobals();

        $turniertag = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Turniertag')
            ->findOneBy(array('id' => $request->request->get('tagId')));

        $em = $this->getDoctrine()->getManager();
        $em->remove($turniertag);
        $em->flush();

        return new Response();
    }

    /**
     * @Route("/turnierplan/erstellung/turniertag/api/add", name="spTurniertagAPIAdd")
     */
    public function turniertagAendernAPIAddAction()
    {
        //$this->denyAccessUnlessGranted('ROLE_TURNIERPLAN_ERSTELLEN');

        $request = Request::createFromGlobals();

        $starttime = new \DateTime();
        $starttime->setTimestamp($request->request->get('dateStartUTC')/1000);
        $endtime = new \DateTime();
        $endtime->setTimestamp($request->request->get('dateEndUTC')/1000);

        $turniertag = new Turniertag();

        $spielplan = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Spielplan')
            ->findOneBy(array('id' => 1));

        $turniertag->setSpielplan($spielplan);
        $turniertag->setUhrzBeginn($starttime);
        $turniertag->setUhrzEnde($endtime);

        $em = $this->getDoctrine()->getManager();
        $em->persist($turniertag);
        $em->flush();

        return new Response();
    }

    /**
     * @Route("/turnierplan/erstellung/turniertag/api/json", name="spTurniertagAPIJson")
     */
    public function turniertagAendernAPIJsonAction()
    {
        //$this->denyAccessUnlessGranted('ROLE_TURNIERPLAN_ERSTELLEN');

        $turniertage = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Turniertag')
            ->findAll();

        $json = '{  "draw": 1,
                    "recordsTotal": 57,
                    "recordsFiltered": 57,
                    "data": [';

        for($i = 0; $i < count($turniertage); $i++)
        {
            $json .= '{ "DT_RowId": "'.$turniertage[$i]->getId().'",
                        "tag": "'.$turniertage[$i]->getUhrzBeginn()->format('d.m.Y').'",
                        "anfang": "'.$turniertage[$i]->getUhrzBeginn()->format('H:i').'",
                        "ende": "'.$turniertage[$i]->getUhrzEnde()->format('H:i').'",
                        "button": "<button class=\"btn btn-default RemoveDaten\" type=\"button\"><i class=\"glyphicon glyphicon-minus-sign\"></i></button>"
                        }';
            if($i+1 != count($turniertage))
                $json .= ',';
        }


        return new Response($json.' ] }');
    }
}