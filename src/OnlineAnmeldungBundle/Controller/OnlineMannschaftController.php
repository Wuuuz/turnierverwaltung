<?php

namespace OnlineAnmeldungBundle\Controller;

use OnlineAnmeldungBundle\Form\OnlineMannschaftType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

class OnlineMannschaftController extends Controller
{
    /**
     * @Route("/onlineanmeldung/mannschaft", name="onlineMannschaftList")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted('ROLE_MANNSCHAFT_VIEW');

        $mannschaften = $this->getDoctrine()
            ->getRepository('TeilnehmerBundle:Mannschaft')
            ->findBy(array('status' => 0));

        return $this->render('OnlineAnmeldungBundle:OnlineMannschaft:list.html.twig', array(
            'mannschaften' => $mannschaften
        ));
    }

    /**
     * @Route("/onlineanmeldung/mannschaft/{id}/edit", name="onlineMannschaftEdit")
     */
    public function editAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_MANNSCHAFT_VIEW');
        $this->denyAccessUnlessGranted('ROLE_MANNSCHAFT_EDIT');

        $mannschaft = $this->getDoctrine()
            ->getRepository('TeilnehmerBundle:Mannschaft')
            ->findOneBy(array('id' => $id));

        if($mannschaft->getStatus()>0)
        {
            $this->addFlash(
                'warning',
                'Mannschaft ist bereits freigegeben!'
            );

            $mannschaften = $this->getDoctrine()
                ->getRepository('TeilnehmerBundle:Mannschaft')
                ->findBy(array('status' => 0));

            return $this->render('OnlineAnmeldungBundle:OnlineMannschaft:list.html.twig', array(
                'mannschaften' => $mannschaften
            ));
        }

        $form = $this->createForm(OnlineMannschaftType::class,$mannschaft);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($form['herkunft']->getData() == 1)
                $staerke = 1000;
            else if ($form['herkunft']->getData() == 2)
                $staerke = 99;
            else if ($form['herkunft']->getData() == 3)
                $staerke = $form['liga']->getData()*10-$form['platz']->getData();

            $mannschaft->setStarke($staerke);
            $mannschaft->setStatus(1);

            $em = $this->getDoctrine()->getManager();
            $em->persist($mannschaft);
            $em->flush();

            $this->addFlash(
                'info',
                'Mannschaft erfolgreich übernommen!'
            );

            if($form->get('sichernUndSchliessen')->isClicked())
                return $this->redirectToRoute('onlineMannschaftList');
            else if($form->get('save')->isClicked())
                return $this->redirectToRoute('mannschaftEdit',array('id' => $mannschaft->getId()));
        }
        else if($form->isSubmitted()){
            $validator = $this->get('validator');
            $errors = $validator->validate($mannschaft);

            return $this->render('OnlineAnmeldungBundle:OnlineMannschaft:edit.html.twig', array(
                'form' => $form->createView(),
                'errors' => $errors,
            ));
        }

        return $this->render('OnlineAnmeldungBundle:OnlineMannschaft:edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/onlineanmeldung/mannschaft/delete", name="onlineMannschaftDelete")
     * @Method({"POST"})
     */
    public function deleteAction()
    {
        if(!$this->isGranted('ROLE_MANNSCHAFT_DELETE')){
            $this->addFlash(
                'danger',
                'Sie besitzen keine Berechtigung, eine Mannschaft zu löschen!'
            );
        }
        else {
            $request = Request::createFromGlobals();

            $mannschaften_delete = $request->request->get('select_all');

            foreach ($mannschaften_delete as $id) {
                $em = $this->getDoctrine()->getManager();

                try {
                    $mannschaft = $this->getDoctrine()
                        ->getRepository('TeilnehmerBundle:Mannschaft')
                        ->findOneBy(array('id' => $id));
                } catch (NotFoundHttpException $e) {
                    $this->addFlash(
                        'warning',
                        'Mannschaft mit ID ' . $id . ' konnte nicht gelöscht werden! Grund: Mannschaft nicht vorhanden'
                    );
                }
                try {
                    $em->remove($mannschaft);
                    $em->flush();
                    $this->addFlash(
                        'info',
                        'Mannschaft mit ID ' . $id . ' erfolgreich gelöscht!'
                    );
                } catch (ForeignKeyConstraintViolationException $e) {
                    $this->addFlash(
                        'danger',
                        'Mannschaft mit ID ' . $id . ' konnte nicht gelöscht werden! Grund: Fremdschlüsselbeziehung'
                    );
                } catch (Exception $e) {
                    $this->addFlash(
                        'danger',
                        'Mannschaft mit ID ' . $id . ' konnte nicht gelöscht werden!'
                    );
                }
            }
        }


        $mannschaften = $this->getDoctrine()
            ->getRepository('TeilnehmerBundle:Mannschaft')
            ->findBy(array('status' => 0));

        return $this->redirectToRoute('onlineMannschaftList' ,array('mannschaften' => $mannschaften));
    }
}
