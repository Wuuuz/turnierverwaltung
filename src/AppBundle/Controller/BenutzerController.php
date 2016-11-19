<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class BenutzerController extends Controller
{
    /**
     * @Route("/benutzer", name="benutzer")
     */
    public function anzeigenAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('@App/benutzer/benutzerListe.html.twig');
    }

    /**
     * @Route("/benutzer/neu", name="benutzerNeu")
     */
    public function neuAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('@App/benutzer/benutzerDetail.html.twig');
    }
}
