<?php
/**
 * Created by PhpStorm.
 * User: Andreas
 * Date: 29.11.2016
 * Time: 19:19
 */

namespace UserBundle\Controller;


use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use FOS\UserBundle\Event\FilterGroupResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseGroupEvent;
use FOS\UserBundle\Event\GroupEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\GroupInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\UserBundle\Controller\RegistrationController as BaseController;
use UserBundle\Entity\Group;
use UserBundle\Form\GroupFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * RESTful controller managing group CRUD.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
class GroupController extends BaseController
{
    /**
     * Show all groups.
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted('ROLE_GROUP_VIEW');

        $gruppen = $this->get('fos_user.group_manager')->findGroups();

        return $this->render('FOSUserBundle:Group:list.html.twig', array(
            'gruppen' => $gruppen,
        ));
    }

    /**
     * Show one group.
     *
     * @param string $groupName
     *
     * @return Response
     */
    public function showAction($id)
    {

        return $this->render('FOSUserBundle:Group:show.html.twig');
    }

    /**
     * Edit one group, show the edit form.
     *
     * @param Request $request
     * @param string  $groupName
     *
     * @return Response
     */
    public function editAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_GROUP_VIEW');
        $this->denyAccessUnlessGranted('ROLE_GROUP_EDIT');

        $roles = $this->getDoctrine()
        ->getRepository('UserBundle:Role')
        ->findAll();

        foreach ($roles as $role)
            $theRoles[$role->getBezeichnung()] = $role->getTechBezeichnung();

        $group = $this->findGroupBy('id', $id);

        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseGroupEvent($group, $request);
        $dispatcher->dispatch(FOSUserEvents::GROUP_EDIT_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->createForm(GroupFormType::class,$group,array('roles' => $theRoles));
        $form->setData($group);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var $groupManager \FOS\UserBundle\Model\GroupManagerInterface */
            $groupManager = $this->get('fos_user.group_manager');

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::GROUP_EDIT_SUCCESS, $event);

            $groupManager->updateGroup($group);

            $this->addFlash(
                'info',
                'Gruppe erfolgreich bearbeitet!'
            );

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('fos_user_group_edit', array('id' => $group->getId()));
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(FOSUserEvents::GROUP_EDIT_COMPLETED, new FilterGroupResponseEvent($group, $request, $response));

            return $response;
        }

        return $this->render('FOSUserBundle:Group:edit.html.twig', array(
            'form' => $form->createView(),
            'group_name' => $group->getName(),
        ));
    }

    /**
     * Show the new form.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_GROUP_CREATE');

        $roles = $this->getDoctrine()
            ->getRepository('UserBundle:Role')
            ->findAll();

        foreach ($roles as $role)
            $theRoles[$role->getBezeichnung()] = $role->getTechBezeichnung();

        /** @var $groupManager \FOS\UserBundle\Model\GroupManagerInterface */
        $groupManager = $this->get('fos_user.group_manager');
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.group.form.factory');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $group = $groupManager->createGroup('');

        $dispatcher->dispatch(FOSUserEvents::GROUP_CREATE_INITIALIZE, new GroupEvent($group, $request));

        $form = $this->createForm(GroupFormType::class, $group, array('roles' => $theRoles));

        $form->setData($group);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::GROUP_CREATE_SUCCESS, $event);

            $groupManager->updateGroup($group);

            $this->addFlash(
                'info',
                'Gruppe erfolgreich hinzugefügt!'
            );

            if (null === $response = $event->getResponse()) {
                if ($form->get('sichernUndSchliessen')->isClicked())
                    $url = $this->generateUrl('fos_user_group_list');
                else if ($form->get('save')->isClicked())
                    $url = $this->generateUrl('fos_user_group_edit', array('id' => $group->getId()));

                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(FOSUserEvents::GROUP_CREATE_COMPLETED, new FilterGroupResponseEvent($group, $request, $response));

            return $response;
        }

        return $this->render('FOSUserBundle:Group:new.html.twig', array(
            'form' => $form->createView(),
        ));

    }

    /**
     * @Route("/gruppe/delete", name="gruppeDelete")
     * @Method({"POST"})
     */
    public function deleteAction()
    {
        if(!$this->isGranted('ROLE_GROUP_DELETE')){
            $this->addFlash(
                'danger',
                'Sie besitzen keine Berechtigung, eine Gruppe zu löschen!'
            );
        }
        else {
            $request = Request::createFromGlobals();

            $gruppe_delete = $request->request->get('select_all');

            foreach ($gruppe_delete as $id) {
                $em = $this->getDoctrine()->getManager();

                try {
                    $gruppe = $this->getDoctrine()
                        ->getRepository('UserBundle:Group')
                        ->findOneBy(array('id' => $id));
                } catch (NotFoundHttpException $e) {
                    $this->addFlash(
                        'warning',
                        'Gruppe mit ID ' . $id . ' konnte nicht gelöscht werden! Grund: Verein nicht vorhanden'
                    );
                }

                try {
                    $em->remove($gruppe);
                    $em->flush();
                    $this->addFlash(
                        'info',
                        'Gruppe mit ID ' . $id . ' erfolgreich gelöscht!'
                    );
                } catch (ForeignKeyConstraintViolationException $e) {
                    $this->addFlash(
                        'danger',
                        'Gruppe mit ID ' . $id . ' konnte nicht gelöscht werden! Grund: Fremdschlüsselbeziehung'
                    );
                } catch (Exception $e) {
                    $this->addFlash(
                        'danger',
                        'Gruppe mit ID ' . $id . ' konnte nicht gelöscht werden!'
                    );
                }
            }
        }

        $gruppen = $this->getDoctrine()
            ->getRepository('UserBundle:Group')
            ->findAll();

        return $this->redirectToRoute('fos_user_group_list',array('gruppen' => $gruppen));
    }

    /**
     * Find a group by a specific property.
     *
     * @param string $key   property name
     * @param mixed  $value property value
     *
     * @throws NotFoundHttpException if user does not exist
     *
     * @return GroupInterface
     */
    protected function findGroupBy($key, $value)
    {
        if (!empty($value)) {
            $group = $this->getDoctrine()
                ->getRepository('UserBundle:Group')
                ->findOneBy(array($key => $value));
        }

        if (empty($group)) {
            throw new NotFoundHttpException(sprintf('The group with "%s" does not exist for value "%s"', $key, $value));
        }

        return $group;
    }
}