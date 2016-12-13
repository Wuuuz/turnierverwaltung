<?php

namespace TeilnehmerBundle\Controller;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use TeilnehmerBundle\Entity\Verein;
use Symfony\Component\HttpFoundation\Request;
use TeilnehmerBundle\Form\VereinType;


class VereinController extends Controller
{
    /**
     * @Route("/verein", name="vereinList")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted('ROLE_VEREIN_VIEW');

        $vereine = $this->getDoctrine()
            ->getRepository('TeilnehmerBundle:Verein')
            ->findAll();

        return $this->render('@Teilnehmer/Verein/list.html.twig', array(
            'vereine' => $vereine
        ));
    }

    /**
     * @Route("/verein/neu", name="vereinNew")
     */
    public function newAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_VEREIN_CREATE');

        $verein = new Verein();

        $form = $this->createForm(VereinType::class,$verein);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
             $em = $this->getDoctrine()->getManager();
             $em->persist($verein);
             $em->flush();

            $this->addFlash(
                'info',
                'Verein erfolgreich hinzugefügt!'
            );

            if($form->get('sichernUndSchliessen')->isClicked())
                return $this->redirectToRoute('vereinList');
            if($form->get('save')->isClicked())
                return $this->redirectToRoute('vereinEdit',array('id' => $verein->getId()));
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
        $this->denyAccessUnlessGranted('ROLE_VEREIN_VIEW');
        $this->denyAccessUnlessGranted('ROLE_VEREIN_EDIT');

        $verein = $this->getDoctrine()
            ->getRepository('TeilnehmerBundle:Verein')
            ->findOneBy(array('id' => $id));

        $form = $this->createForm(VereinType::class, $verein);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($verein);
            $em->flush();

            $this->addFlash(
                'info',
                'Verein erfolgreich bearbeitet!'
            );

            if($form->get('sichernUndSchliessen')->isClicked())
                return $this->redirectToRoute('vereinUebersicht');
            if($form->get('save')->isClicked())
                return $this->redirectToRoute('vereinEdit',array('id' => $verein->getId()));
        }
        else if($form->isSubmitted()){
            $validator = $this->get('validator');
            $errors = $validator->validate($verein);

            return $this->render('@Teilnehmer/Verein/new.html.twig', array(
                'form' => $form->createView(),
                'errors' => $errors,
            ));
        }

        return $this->render('TeilnehmerBundle:Verein:edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/verein/delete", name="vereinDelete")
     * @Method({"POST"})
     */
    public function deleteAction()
    {
        if(!$this->isGranted('ROLE_VEREIN_DELETE')){
            $this->addFlash(
                'danger',
                'Sie besitzen keine Berechtigung, einen Verein zu löschen!'
            );
        }
        else {

            $request = Request::createFromGlobals();

            $vereine_delete = $request->request->get('select_all');

            foreach ($vereine_delete as $id) {
                $em = $this->getDoctrine()->getManager();

                try {
                    $verein = $this->getDoctrine()
                        ->getRepository('TeilnehmerBundle:Verein')
                        ->findOneBy(array('id' => $id));
                } catch (NotFoundHttpException $e) {
                    $this->addFlash(
                        'warning',
                        'Verein mit ID ' . $id . ' konnte nicht gelöscht werden! Grund: Verein nicht vorhanden'
                    );
                }

                try {
                    $em->remove($verein);
                    $em->flush();
                    $this->addFlash(
                        'info',
                        'Verein mit ID ' . $id . ' erfolgreich gelöscht!'
                    );
                } catch (ForeignKeyConstraintViolationException $e) {
                    $this->addFlash(
                        'danger',
                        'Verein mit ID ' . $id . ' konnte nicht gelöscht werden! Grund: Fremdschlüsselbeziehung'
                    );
                } catch (Exception $e) {
                    $this->addFlash(
                        'danger',
                        'Verein mit ID ' . $id . ' konnte nicht gelöscht werden!'
                    );
                }
            }
        }


        $vereine = $this->getDoctrine()
            ->getRepository('TeilnehmerBundle:Verein')
            ->findAll();

        return $this->redirectToRoute('vereinList',array('vereine' => $vereine));
    }
}
