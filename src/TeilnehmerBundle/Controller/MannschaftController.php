<?php

namespace TeilnehmerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class MannschaftController extends Controller
{
    /**
     * @Route("/mannschaft", name="mannschaftList")
     */
    public function listAction()
    {
        return $this->render('TeilnehmerBundle:Mannschaft:list.html.twig');
    }

    /**
     * @Route("/mannschaft/neu", name="mannschaftNew")
     */
    public function newAction()
    {
        return $this->render('@Teilnehmer/Mannschaft/new.html.twig');
    }

    /**
     * @Route("/mannschaft/{id}/edit", name="mannschaftEdit")
     */
    public function editAction($id)
    {
        return $this->render('@Teilnehmer/Mannschaft/edit.html.twig');
    }
}
