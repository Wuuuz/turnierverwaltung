<?php

namespace TeilnehmerBundle\Controller;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TeilnehmerBundle\Entity\Schiedsrichter;
use TeilnehmerBundle\Form\SchiedsrichterType;

class SchiedsrichterController extends Controller
{
    /**
     * @Route("/schiedsrichter", name="schiedsrichterList")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted('ROLE_SR_VIEW');

        $sr = $this->getDoctrine()
            ->getRepository('TeilnehmerBundle:Schiedsrichter')
            ->findAll();

        return $this->render('TeilnehmerBundle:Schiedsrichter:list.html.twig', array(
            'schiedsrichter' => $sr
        ));
    }

    /**
     * @Route("/schiedsrichter/neu", name="schiedsrichterNew")
     */
    public function newAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_SR_CREATE');

        $sr = new Schiedsrichter();

        $form = $this->createForm(SchiedsrichterType::class,$sr);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($sr);
            $em->flush();

            $this->addFlash(
                'info',
                'Schiedsrichter erfolgreich angelegt!'
            );

            if($form->get('sichernUndSchliessen')->isClicked())
                return $this->redirectToRoute('schiedsrichterList');
            if($form->get('save')->isClicked())
                return $this->redirectToRoute('schiedsrichterEdit',array('id' => $sr->getId()));
        }
        else if($form->isSubmitted()){
            $validator = $this->get('validator');
            $errors = $validator->validate($sr);

            return $this->render('TeilnehmerBundle:Schiedsrichter:new.html.twig', array(
                'form' => $form->createView(),
                'errors' => $errors,
            ));
        }

        return $this->render('TeilnehmerBundle:Schiedsrichter:new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/schiedsrichter/{id}/edit", name="schiedsrichterEdit")
     */
    public function editAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_SR_VIEW');
        $this->denyAccessUnlessGranted('ROLE_SR_EDIT');

        $sr = $this->getDoctrine()
            ->getRepository('TeilnehmerBundle:Schiedsrichter')
            ->findOneBy(array('id' => $id));

        $form = $this->createForm(SchiedsrichterType::class,$sr);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($sr);
            $em->flush();

            $this->addFlash(
                'info',
                'Schiedsrichter erfolgreich bearbeitet!'
            );

            if($form->get('sichernUndSchliessen')->isClicked())
                return $this->redirectToRoute('schiedsrichterList');
            if($form->get('save')->isClicked())
                return $this->redirectToRoute('schiedsrichterEdit',array('id' => $sr->getId()));
        }
        else if($form->isSubmitted()){
            $validator = $this->get('validator');
            $errors = $validator->validate($sr);

            return $this->render('TeilnehmerBundle:Schiedsrichter:edit.html.twig', array(
                'form' => $form->createView(),
                'errors' => $errors,
            ));
        }

        return $this->render('TeilnehmerBundle:Schiedsrichter:edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/schiedsrichter/delete", name="schiedsrichterDelete")
     * @Method({"POST"})
     */
    public function deleteAction()
    {
        if(!$this->isGranted('ROLE_SR_DELETE')){
            $this->addFlash(
                'danger',
                'Sie besitzen keine Berechtigung, einen Schiedsrichter zu löschen!'
            );
        }
        else {

            $request = Request::createFromGlobals();

            $sr_delete = $request->request->get('select_all');

            foreach ($sr_delete as $id) {
                $em = $this->getDoctrine()->getManager();

                try {
                    $sr = $this->getDoctrine()
                        ->getRepository('TeilnehmerBundle:Schiedsrichter')
                        ->findOneBy(array('id' => $id));
                } catch (NotFoundHttpException $e) {
                    $this->addFlash(
                        'warning',
                        'Schiedsrichter mit ID ' . $id . ' konnte nicht gelöscht werden! Grund: Verein nicht vorhanden'
                    );
                }

                try {
                    $em->remove($sr);
                    $em->flush();
                    $this->addFlash(
                        'info',
                        'Schiedsrichter mit ID ' . $id . ' erfolgreich gelöscht!'
                    );
                } catch (ForeignKeyConstraintViolationException $e) {
                    $this->addFlash(
                        'danger',
                        'Schiedsrichter mit ID ' . $id . ' konnte nicht gelöscht werden! Grund: Fremdschlüsselbeziehung'
                    );
                } catch (Exception $e) {
                    $this->addFlash(
                        'danger',
                        'Schiedsrichter mit ID ' . $id . ' konnte nicht gelöscht werden!'
                    );
                }
            }
        }

        $schiedsrichter = $this->getDoctrine()
            ->getRepository('TeilnehmerBundle:Schiedsrichter')
            ->findAll();

        return $this->redirectToRoute('schiedsrichterList',array('schiedsrichter' => $schiedsrichter));
    }
}
