<?php

namespace OnlineAnmeldungBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TeilnehmerBundle\Entity\Mannschaft;
use TeilnehmerBundle\Entity\Verein;

class OnlineAPIController extends Controller
{
    /**
     * @Route("/onlineanmeldung/api", name="onlineAnmeldungAPI")
     */
    public function listAction()
    {
        $request = Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();

        //Verein anlegen
        $verein = $this->getDoctrine()
            ->getRepository('TeilnehmerBundle:Verein')
            ->findBy(array('name' => $request->request->get('vereinsname')));

        if ($request->request->get('vereinsname') == "")
            return new Response('Vereinsname equals null',Response::HTTP_BAD_REQUEST);
        else if (count($verein) == 0)
        {
            $neuerVerein = new Verein();
            $neuerVerein
                ->setName($request->request->get('vereinsname'))
                ->setStatus(0);
            $em->persist($neuerVerein);

            $verein = $neuerVerein;
        }

        $mannschaften = $request->request->get('mannschaften');

        //Mannschaften anelgen
        for($i = 0; $i < count($mannschaften); $i++)
        {
            for($j = 0; $j < count($mannschaften[$i]); $j++)
                if ($mannschaften[$i][$j] == "")
                    return new Response('Variable '.$j.' of team '.$i.' equals null',Response::HTTP_BAD_REQUEST);

            $mannschaftNeu = new Mannschaft();
            $mannschaftNeu
                ->setStatus(0)
                ->setName($mannschaften[$i][0])
                ->setAltersklasse($this->getDoctrine()
                    ->getRepository('TurnierplanBundle:Altersklasse')
                    ->findOneBy(array('id' => $mannschaften[$i][1])))
                ->setAnzPersonen($mannschaften[$i][2])
                ->setAnkunft(date_create_from_format('d.m.Y H:i',$mannschaften[$i][3]))
                ->setUnterkunft($this->getDoctrine()
                    ->getRepository('TurnierplanBundle:Altersklasse')
                    ->findOneBy(array('id' => $mannschaften[$i][4])))
                ->setAnzSr($mannschaften[$i][5])
                ->setAnzEssen($mannschaften[$i][6])
                ->setVerein($verein)
                ->setMvName($request->request->get('ansprechpartner'))
                ->setMvEmail($request->request->get('email'))
                ->setMvTelefon($request->request->get('telefonnummer'))
                ->setMvPLZ($request->request->get('plz'))
                ->setMvOrt($request->request->get('ort'))
                ->setMvLand($request->request->get('land'))
                ->setMvStrasse($request->request->get('strasse'));

            $em->persist($mannschaftNeu);
        }

        $em->flush();

        return new Response(count($mannschaften));
    }
}
