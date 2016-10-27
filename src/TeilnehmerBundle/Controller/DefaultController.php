<?php

namespace TeilnehmerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Validator\Constraints\Date;
use TurnierplanBundle\Entity\Spielplan;
use TurnierplanBundle\Entity\Turniertag;
use TurnierplanBundle\Entity\Vorrundenspiel;

class DefaultController extends Controller
{
    /**
     * @Route("/default")
     */
    public function indexAction()
    {
        $product = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Spielplan')
            ->find(1);

        $datum = new \DateTime();
        $datum->setDate(2016,10,20);

        $spielplan = new Turniertag();
        $spielplan->setDatum($datum);
        $spielplan->setSpielplan($product);
        $spielplan->setUhrzBeginn(1);
        $spielplan->setUhrzEnde(2);

        $em = $this->getDoctrine()->getManager();

        // tells Doctrine you want to (eventually) save the Product (no queries yet)
        $em->persist($spielplan);

        // actually executes the queries (i.e. the INSERT query)
        $em->flush();

        return $this->render('TeilnehmerBundle:Default:index.html.twig');
    }
}
