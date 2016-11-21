<?php

namespace TeilnehmerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class MannschaftController extends Controller
{
    /**
     * @Route("/mannschaft", name="mannschaftUebersicht")
     */
    public function uebersichtAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('TeilnehmerBundle:Mannschaft:mannschaftUebersicht.html.twig');
    }
}
