<?php

namespace OnlineAnmeldungBundle\Controller;

use OnlineAnmeldungBundle\Form\OnlineVereinType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

class OnlineVereinController extends Controller
{
    /**
     * @Route("/onlineanmeldung/verein", name="onlineVereinList")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted('ROLE_VEREIN_VIEW');

        $vereine = $this->getDoctrine()
            ->getRepository('TeilnehmerBundle:Verein')
            ->findBy(array('status' => 0));

        return $this->render('@OnlineAnmeldung/OnlineVerein/list.html.twig', array(
            'vereine' => $vereine
        ));
    }

    /**
     * @Route("/onlineanmeldung/verein/{id}/edit", name="onlineVereinEdit")
     */
    public function editAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_VEREIN_VIEW');
        $this->denyAccessUnlessGranted('ROLE_VEREIN_EDIT');

        $verein = $this->getDoctrine()
            ->getRepository('TeilnehmerBundle:Verein')
            ->findOneBy(array('id' => $id));

        if($verein->getStatus()>0)
        {
            $this->addFlash(
                'warning',
                'Verein ist bereits freigegeben!'
            );

            $vereine = $this->getDoctrine()
                ->getRepository('TeilnehmerBundle:Verein')
                ->findBy(array('status' => 0));

            return $this->render('@OnlineAnmeldung/OnlineVerein/list.html.twig', array(
                'vereine' => $vereine
            ));
        }

        $form = $this->createForm(OnlineVereinType::class,$verein);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $verein->setStatus(1);

            $em = $this->getDoctrine()->getManager();
            $em->persist($verein);
            $em->flush();

            $this->addFlash(
                'info',
                'Verein erfolgreich übernommen!'
            );

            if($form->get('sichernUndSchliessen')->isClicked())
                return $this->redirectToRoute('onlineVereinList');
            else if($form->get('save')->isClicked())
                return $this->redirectToRoute('vereinEdit',array('id' => $verein->getId()));
        }
        else if($form->isSubmitted()){
            $validator = $this->get('validator');
            $errors = $validator->validate($verein);

            return $this->render('OnlineAnmeldungBundle:OnlineVerein:edit.html.twig', array(
                'form' => $form->createView(),
                'errors' => $errors,
            ));
        }

        return $this->render('OnlineAnmeldungBundle:OnlineVerein:edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/onlineanmeldung/verein/delete", name="onlineVereinDelete")
     * @Method({"POST"})
     */
    public function deleteAction()
    {
        if(!$this->isGranted('ROLE_MANNSCHAFT_DELETE')){
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
            ->findBy(array('status' => 0));

        return $this->redirectToRoute('onlineVereinList' ,array('vereine' => $vereine));
    }
}
