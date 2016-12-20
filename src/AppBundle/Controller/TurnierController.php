<?php

namespace AppBundle\Controller;

use AppBundle\Form\TurnierType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TurnierController extends Controller
{
    /**
     * @Route("/turnier", name="turnierEdit")
     */
    public function editAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_VEREIN_VIEW');

        $turnier = $this->getDoctrine()
            ->getRepository('AppBundle:Turnier')
            ->findOneBy(array('id' => 1));

        if(!$this->isGranted('ROLE_TURNIER_EDIT'))
        {
            $form = $this->createForm(TurnierType::class, $turnier,array('button' => false));

            return $this->render('@App/Turnier/view.html.twig', array(
                'form' => $form->createView(),
            ));
        }

        $form = $this->createForm(TurnierType::class, $turnier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($turnier);
            $em->flush();

            $this->addFlash(
                'info',
                'Turnier erfolgreich bearbeitet!'
            );

            if($form->get('save')->isClicked())
                return $this->redirectToRoute('turnierEdit',array('id' => 1 ));
        }
        else if($form->isSubmitted()){
            $validator = $this->get('validator');
            $errors = $validator->validate($turnier);

            return $this->render('@App/Turnier/edit.html.twig', array(
                'form' => $form->createView(),
                'errors' => $errors,
            ));
        }

        return $this->render('@App/Turnier/edit.html.twig',
            array('form' => $form->createView(),
        ));

    }
}
