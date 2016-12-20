<?php

namespace AppBundle\Controller;

use AppBundle\Form\TurnierType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ResetController extends Controller
{
    /**
     * @Route("/turnier/reset", name="turnierReset")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted('ROLE_TURNIER_DELETE');

        return $this->render('@App/Reset/list.html.twig');
    }

    /**
     * @Route("/turnier/reset/delete", name="turnierDelete")
     */
    public function deleteAction()
    {
        $this->denyAccessUnlessGranted('ROLE_TURNIER_DELETE');

        $request = Request::createFromGlobals();

        $turnier_delete = $request->request->get('turnierReset');

        $em = $this->getDoctrine()->getManager();

        foreach ($turnier_delete as $id) {
            switch($id){
                case 1:{
                    $turnier = $this->getDoctrine()
                        ->getRepository('AppBundle:Turnier')
                        ->findOneBy(array('id' => 1));

                    $turnier->setName(null);

                    $em->persist($turnier);
                    $em->flush();

                    $this->addFlash(
                        'info',
                        'Turnierinformationen wurden zurückgesetzt!'
                    );
                    break;
                }
                case 2:{
                    $this->truncateTable(7);
                    $this->truncateTable(8);
                    $this->truncateTable(9);
                    $this->truncateTable(10);
                    $this->truncateTable(1);
                    $this->truncateTable(2);
                    $this->truncateTable(3);

                    $turnier = $this->getDoctrine()
                        ->getRepository('AppBundle:Turnier')
                        ->findOneBy(array('id' => 1));

                    $turnier->setName(null);

                    $em->persist($turnier);
                    $em->flush();

                    $this->addFlash(
                        'info',
                        'Turnierinformationen wurden zurückgesetzt!'
                    );
                    break;

                    break;
                }
                case 3:{
                    break;
                }
                case 4:{
                    break;
                }
                case 5:{
                    $this->truncateTable(7);
                    $this->truncateTable(8);
                    $this->truncateTable(9);
                    $this->truncateTable(10);
                    $this->truncateTable(1);
                    break;
                }
                case 6:{
                    $this->truncateTable(7);
                    $this->truncateTable(8);
                    $this->truncateTable(9);
                    $this->truncateTable(10);
                    $this->truncateTable(1);
                    $this->truncateTable(2);
                    $this->truncateTable(3);
                    break;
                }
                case 7: {
                    $this->truncateTable(2);
                    break;
                }
                case 8:
                {
                    $this->truncateTable(7);
                    $this->truncateTable(8);
                    $this->truncateTable(9);
                    $this->truncateTable(10);
                    break;
                }
                case 9:{
                    $spiele = $this->getDoctrine()
                        ->getRepository('TurnierplanBundle:Spiel')
                        ->findAll();

                    foreach ($spiele as $spiel) {
                        $spiel->setErgGast(null);
                        $spiel->setErgHeim(null);

                        $em->persist($spiel);
                    }
                    $em->flush();

                    $this->addFlash(
                        'info',
                        'Spielergebnisse wurden zurückgesetzt!'
                    );

                    break;
                }
                case 10:{
                    $spiele = $this->getDoctrine()
                        ->getRepository('TurnierplanBundle:Spiel')
                        ->findAll();

                    foreach ($spiele as $spiel) {
                        $spiel->setSchiedsrichter1(null);
                        $spiel->setSchiedsrichter2(null);

                        $em->persist($spiel);
                    }
                    $em->flush();

                    $this->addFlash(
                        'info',
                        'Schiedsrichtereinteilungen wurden zurückgesetzt!'
                    );

                    break;
                }
            }
        }

        return $this->redirectToRoute('turnierReset');
    }

    public function truncateTable ($table){
            $em = $this->getDoctrine()->getManager();
            try{
                switch ($table){
                    case 1:{
                        $cmd = $em->getClassMetadata('TeilnehmerBundle:Mannschaft');
                        $this->addFlash(
                            'info',
                            'Mannschaften wurden zurückgesetzt!'
                        );
                        break;
                    }
                    case 2:{
                        $cmd = $em->getClassMetadata('TeilnehmerBundle:Schiedsrichter');
                        $this->addFlash(
                            'info',
                            'Schiedsrichter wurden zurückgesetzt!'
                        );
                        break;
                    }
                    case 3:{
                        $cmd = $em->getClassMetadata('TeilnehmerBundle:Verein');
                        $this->addFlash(
                            'info',
                            'Vereine wurden zurückgesetzt!'
                        );
                        break;
                    }
                    case 7:{
                        $cmd = $em->getClassMetadata('TurnierplanBundle:Spiel');
                        $this->addFlash(
                            'info',
                            'Spiele wurden zurückgesetzt!'
                        );
                        break;
                    }
                    case 8:{
                        $cmd = $em->getClassMetadata('TurnierplanBundle:Vorrundenspiel');
                        break;
                    }
                    case 9:{
                        $cmd = $em->getClassMetadata('TurnierplanBundle:Hauptrundenspiel');
                        break;
                    }
                    case 10:{
                        $cmd = $em->getClassMetadata('TurnierplanBundle:Endrundenspiel');
                        break;
                    }

                }

                $connection = $em->getConnection();
                $dbPlatform = $connection->getDatabasePlatform();
                $connection->beginTransaction();

                $connection->query('SET FOREIGN_KEY_CHECKS=0');
                $q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
                $connection->executeUpdate($q);
                $connection->query('SET FOREIGN_KEY_CHECKS=1');
                $connection->commit();
            }
            catch(\Exception $e){
                $this->addFlash(
                    'danger',
                    'Ein Fehler ist während des Zurücksetzens aufgetreten! '.$e->getMessage()
                );
            }
    }
}
