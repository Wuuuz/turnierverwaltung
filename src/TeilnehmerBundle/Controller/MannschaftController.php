<?php

namespace TeilnehmerBundle\Controller;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TeilnehmerBundle\Entity\Mannschaft;
use TeilnehmerBundle\Form\MannschaftType;

class MannschaftController extends Controller
{
    /**
     * @Route("/mannschaft", name="mannschaftList")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted('ROLE_MANNSCHAFT_VIEW');

        $mannschaften = $this->getDoctrine()
            ->getRepository('TeilnehmerBundle:Mannschaft')
            ->findAll();

        return $this->render('TeilnehmerBundle:Mannschaft:list.html.twig', array(
            'mannschaften' => $mannschaften
        ));
    }

    /**
     * @Route("/mannschaft/neu", name="mannschaftNew")
     */
    public function newAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_MANNSCHAFT_CREATE');

        $mannschaft = new Mannschaft();

        $form = $this->createForm(MannschaftType::class,$mannschaft);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($form['herkunft']->getData() == 1)
                $staerke = 1000;
            else if ($form['herkunft']->getData() == 2)
                $staerke = 99;
            else if ($form['herkunft']->getData() == 3)
                $staerke = $form['liga']->getData()*10-$form['ligaplatz']->getData();

            $mannschaft->setStarke($staerke);
            $mannschaft->setStatus(1);

            $em = $this->getDoctrine()->getManager();
            try {
                $em->persist($mannschaft);
                $em->flush();
            }
            catch(UniqueConstraintViolationException $e)
            {
                $this->addFlash(
                    'danger',
                    'Ein eindeutiges Attribut wurde doppelt vergeben!'
                );

                return $this->render('TeilnehmerBundle:Mannschaft:new.html.twig', array(
                    'form' => $form->createView()
                ));
            }

            $this->addFlash(
                'info',
                'Mannschaft erfolgreich hinzugefügt!'
            );

            if($form->get('sichernUndSchliessen')->isClicked())
                return $this->redirectToRoute('mannschaftList');
            else if($form->get('save')->isClicked())
                return $this->redirectToRoute('mannschaftEdit',array('id' => $mannschaft->getId()));
        }
        else if($form->isSubmitted()){
            $validator = $this->get('validator');
            $errors = $validator->validate($mannschaft);

            return $this->render('TeilnehmerBundle:Mannschaft:new.html.twig', array(
                'form' => $form->createView(),
                'errors' => $errors,
            ));
        }

        return $this->render('TeilnehmerBundle:Mannschaft:new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/mannschaft/{id}/edit", name="mannschaftEdit")
     */
    public function editAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_MANNSCHAFT_VIEW');
        $this->denyAccessUnlessGranted('ROLE_MANNSCHAFT_EDIT');

        $mannschaft = $this->getDoctrine()
            ->getRepository('TeilnehmerBundle:Mannschaft')
            ->findOneBy(array('id' => $id));

        $form = $this->createForm(MannschaftType::class,$mannschaft);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($form['herkunft']->getData() == 1)
                $staerke = 1000;
            else if ($form['herkunft']->getData() == 2)
                $staerke = 99;
            else if ($form['herkunft']->getData() == 3)
                $staerke = $form['liga']->getData()*10-$form['platz']->getData();

            $mannschaft->setStarke($staerke);

            $em = $this->getDoctrine()->getManager();

            try {
                $em->persist($mannschaft);
                $em->flush();
            }
            catch(UniqueConstraintViolationException $e)
            {
                $this->addFlash(
                    'danger',
                    'Ein eindeutiges Attribut wurde doppelt vergeben!'
                );

                return $this->render('TeilnehmerBundle:Mannschaft:edit.html.twig', array(
                    'form' => $form->createView()
                ));
            }

            $this->addFlash(
                'info',
                'Mannschaft erfolgreich bearbeitet!'
            );

            if($form->get('sichernUndSchliessen')->isClicked())
                return $this->redirectToRoute('mannschaftList');
        }
        else if($form->isSubmitted()){
            $validator = $this->get('validator');
            $errors = $validator->validate($mannschaft);

            return $this->render('TeilnehmerBundle:Mannschaft:new.html.twig', array(
                'form' => $form->createView(),
                'errors' => $errors,
            ));
        }

        return $this->render('TeilnehmerBundle:Mannschaft:edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/mannschaft/delete", name="mannschaftDelete")
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
                        'Mannschaft mit ID ' . $id . ' konnte nicht gelöscht werden! Grund: Verein nicht vorhanden'
                    );
                }
                try {
                    $em->remove($mannschaft);
                    $em->flush();
                    $this->addFlash(
                        'info',
                        'Mannschaft ' . $mannschaft->getName() . ' erfolgreich gelöscht!'
                    );
                } catch (ForeignKeyConstraintViolationException $e) {
                    $this->addFlash(
                        'danger',
                        'Mannschaft ' . $mannschaft->getName() . ' konnte nicht gelöscht werden! Grund: Fremdschlüsselbeziehung'
                    );
                } catch (Exception $e) {
                    $this->addFlash(
                        'danger',
                        'Mannschaft ' . $mannschaft->getName() . ' konnte nicht gelöscht werden!'
                    );
                }
            }
        }


        $mannschaften = $this->getDoctrine()
            ->getRepository('TeilnehmerBundle:Mannschaft')
            ->findAll();

        return $this->redirectToRoute('mannschaftList',array('mannschaften' => $mannschaften));
    }
}
