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
     * @Route("/turnierplan/erstellung/turniertag/api", name="spTurniertagAPI")
     */
    public function turniertagAendernAPIAction()
    {
        $request = Request::createFromGlobals();

        echo $request->request->get('dateStartUTC');

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
}