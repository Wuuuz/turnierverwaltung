<?php
/**
 * Created by PhpStorm.
 * User: Andreas
 * Date: 30.11.2016
 * Time: 19:41
 */

namespace UserBundle\Controller;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use UserBundle\Form\UserFormType;

/**
 * Controller managing the registration.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
class UserController extends Controller
{
    /**
     * @param Request $request
     *
     * @Route("/benutzer/neu", name="benutzerNew")
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER_CREATE');

        /** @var $userManager UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        //$dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        $form = $this->createForm(UserFormType::class,$user,array('password' => true, 'information' => true));
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $event = new FormEvent($form, $request);
                //$dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

                try {
                    $userManager->updateUser($user);
                }
                catch(UniqueConstraintViolationException $e)
                {
                    $this->addFlash(
                        'danger',
                        'Ein eindeutiges Attribut wurde doppelt vergeben!'
                    );

                    return $this->render('@User/User/new.html.twig', array(
                        'form' => $form->createView(),
                    ));
                }

                $this->addFlash(
                    'info',
                    'Benutzer erfolgreich hinzugefügt!'
                );

                if (null === $response = $event->getResponse()) {
                    if($form->get('sichernUndSchliessen')->isClicked())
                        $url = $this->generateUrl('benutzerList');
                    else if($form->get('save')->isClicked())
                        $url = $this->generateUrl('benutzerEdit', array('id' => $user->getId()));
                    $response = new RedirectResponse($url);
                }

                return $response;
            }

            $event = new FormEvent($form, $request);
            //$dispatcher->dispatch(FOSUserEvents::REGISTRATION_FAILURE, $event);

            if (null !== $response = $event->getResponse()) {
                return $response;
            }
        }

        return $this->render('@User/User/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @param Request $request
     *
     * @Route("/benutzer/{id}/edit", name="benutzerEdit")
     *
     * @return Response
     */
    public function editAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_USER_VIEW');
        $this->denyAccessUnlessGranted('ROLE_USER_EDIT');

        $user = $this->findUserBy('id', $id);

        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        //$dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        $form = $this->createForm(UserFormType::class,$user,array('password' => false, 'information' => true));
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var $userManager UserManagerInterface */
            $userManager = $this->get('fos_user.user_manager');

            $event = new FormEvent($form, $request);
            //$dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

            try {
                $userManager->updateUser($user);
            }
            catch(UniqueConstraintViolationException $e)
            {
                $this->addFlash(
                    'danger',
                    'Ein eindeutiges Attribut wurde doppelt vergeben!'
                );

                return $this->render('@User/User/edit.html.twig', array(
                    'form' => $form->createView(),
                ));
            }

            $this->addFlash(
                'info',
                'Benutzer erfolgreich bearbeitet!'
            );

            if (null === $response = $event->getResponse()) {
                if($form->get('sichernUndSchliessen')->isClicked())
                    $url = $this->generateUrl('benutzerList');
                else if($form->get('save')->isClicked())
                    $url = $this->generateUrl('benutzerEdit', array('id' => $user->getId()));
                $response = new RedirectResponse($url);
            }

            //$dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        }

        return $this->render('@User/User/edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @param Request $request
     *
     * @Route("/benutzer/{id}/edit/passwort", name="benutzerPwEdit")
     *
     * @return Response
     */
    public function pweditAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_USER_VIEW');
        $this->denyAccessUnlessGranted('ROLE_USER_PWEDIT');

        $user = $this->findUserBy('id', $id);

        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        //$dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_INITIALIZE, $event);

        $form = $this->createForm(UserFormType::class,$user,array('password' => true, 'information' => false));
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $event = new FormEvent($form, $request);
            //$dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_SUCCESS, $event);

            $userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $this->addFlash(
                    'info',
                    'Passwort erfolgreich geändert'
                );

                if($form->get('sichernUndSchliessen')->isClicked())
                    $url = $this->generateUrl('benutzerList');
                else if($form->get('save')->isClicked())
                    $url = $this->generateUrl('benutzerPwEdit', array('id' => $user->getId()));

                $response = new RedirectResponse($url);
            }

            return $response;
        }

        return $this->render('@User/User/passwordEdit.html.twig', array(
            'form' => $form->createView(),
            'username' => $user->getName(),
        ));
    }

    /**
     * @param Request $request
     *
     * @Route("/benutzer", name="benutzerList")
     *
     * @return Response
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted('ROLE_USER_VIEW');

        $benutzer = $this->getDoctrine()
            ->getRepository('UserBundle:User')
            ->findAll();

        return $this->render('@User/User/list.html.twig', array(
            'benutzer' => $benutzer
        ));
    }


    /**
     * @Route("/benutzer/delete", name="benutzerDelete")
     * @Method({"POST"})
     */
    public function deleteAction()
    {
        if(!$this->isGranted('ROLE_USER_DELETE')){
            $this->addFlash(
                'danger',
                'Sie besitzen keine Berechtigung, einen Benutzer zu löschen!'
            );
        }
        else {
            $request = Request::createFromGlobals();

            $benutzer_delete = $request->request->get('select_all');

            foreach ($benutzer_delete as $id) {
                $em = $this->getDoctrine()->getManager();

                try {
                    $user = $this->getDoctrine()
                        ->getRepository('UserBundle:User')
                        ->findOneBy(array('id' => $id));
                } catch (NotFoundHttpException $e) {
                    $this->addFlash(
                        'warning',
                        'Benutzer mit ID ' . $id . ' konnte nicht gelöscht werden! Grund: Verein nicht vorhanden'
                    );
                }

                try {
                    $em->remove($user);
                    $em->flush();
                    $this->addFlash(
                        'info',
                        'Benutzer ' . $user->getName() . ' erfolgreich gelöscht!'
                    );
                } catch (ForeignKeyConstraintViolationException $e) {
                    $this->addFlash(
                        'danger',
                        'Benutzer ' . $user->getName() . ' konnte nicht gelöscht werden! Grund: Fremdschlüsselbeziehung'
                    );
                } catch (Exception $e) {
                    $this->addFlash(
                        'danger',
                        'Benutzer ' . $user->getName() . ' konnte nicht gelöscht werden!'
                    );
                }
            }
        }


        $benutzer = $this->getDoctrine()
            ->getRepository('UserBundle:User')
            ->findAll();

        return $this->redirectToRoute('benutzerList',array('benutzer' => $benutzer));
    }


    protected function findUserBy($key, $value)
    {
        if (!empty($value)) {
            $user = $this->getDoctrine()
                ->getRepository('UserBundle:User')
                ->findOneBy(array($key => $value));
        }

        if (empty($user)) {
            throw new NotFoundHttpException(sprintf('The user with "%s" does not exist for value "%s"', $key, $value));
        }

        return $user;
    }
}