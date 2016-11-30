<?php

namespace TeilnehmerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SchiedsrichterController extends Controller
{
    /**
     * @Route("/schiedsrichter", name="schiedsrichterList")
     */
    public function listAction()
    {
        return $this->render('TeilnehmerBundle:Schiedsrichter:list.html.twig');
    }

    /**
     * @Route("/schiedsrichter/neu", name="schiedsrichterNew")
     */
    public function newAction()
    {
        return $this->render('@Teilnehmer/Schiedsrichter/new.html.twig');
    }

    /**
     * @Route("/schiedsrichter/{id}/edit", name="schiedsrichterEdit")
     */
    public function editAction($id)
    {
        return $this->render('@Teilnehmer/Schiedsrichter/edit.html.twig');
    }
}
