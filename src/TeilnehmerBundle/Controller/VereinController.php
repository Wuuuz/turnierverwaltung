<?php

namespace TeilnehmerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use TeilnehmerBundle\Entity\Verein;
use Symfony\Component\HttpFoundation\Request;
use TeilnehmerBundle\Form\VereinType;


class VereinController extends Controller
{
    /**
     * @Route("/verein", name="vereinList")
     */
    public function uebersichtAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('@Teilnehmer/Verein/list.html.twig');
    }

    /**
     * @Route("/verein/neu", name="vereinNew")
     */
    public function neuAction(Request $request)
    {
        $verein = new Verein();

        $form = $this->createForm(VereinType::class,$verein);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
             $em = $this->getDoctrine()->getManager();
             $em->persist($verein);
             $em->flush();

            if($form->get('sichernUndSchliessen')->isClicked())
                return $this->redirectToRoute('vereinUebersicht');
        }
        else if($form->isSubmitted()){
            $validator = $this->get('validator');
            $errors = $validator->validate($verein);

            return $this->render('TeilnehmerBundle:Verein:vereinNeu.html.twig', array(
                'form' => $form->createView(),
                'errors' => $errors,
            ));
        }

        return $this->render('TeilnehmerBundle:Verein:new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/verein/{id}/edit", name="vereinEdit")
     */
    public function editAction($id,Request $request)
    {
        $verein = $this->getDoctrine()
            ->getRepository('TeilnehmerBundle:Verein')
            ->findOneBy(array('id' => $id));

        $form = $this->createForm(VereinType::class, $verein);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($verein);
            $em->flush();

            if($form->get('sichernUndSchliessen')->isClicked())
                return $this->redirectToRoute('vereinUebersicht');
        }
        else if($form->isSubmitted()){
            $validator = $this->get('validator');
            $errors = $validator->validate($verein);

            return $this->render('TeilnehmerBundle:Verein:vereinNeu.html.twig', array(
                'form' => $form->createView(),
                'errors' => $errors,
            ));
        }

        return $this->render('TeilnehmerBundle:Verein:edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
