<?php
/**
 * Created by PhpStorm.
 * User: Andreas
 * Date: 06.12.2016
 * Time: 13:34
 */

namespace TurnierplanBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use TeilnehmerBundle\Entity\Mannschaft;
use TeilnehmerBundle\Utils\AltersklasseAnmeldungWrapper;
use TurnierplanBundle\Entity\Endrundenspiel;
use TurnierplanBundle\Entity\Hauptrundenspiel;
use TurnierplanBundle\Entity\Spiel;
use TurnierplanBundle\Entity\Vorrundenspiel;

class SpieleUmverteilenController extends Controller
{
    /**
     * @Route("/turnierplan/erstellung/4/pausen/api/add", name="spPausenAPIAdd")
     */
    public function newAction()
    {
        $request = Request::createFromGlobals();
        $beginnID = $request->request->get('spielId');

        $em = $this->getDoctrine()->getEntityManager();

        $spielplan = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Spielplan')
            ->findOneBy(array('id' => 1));

        $spiel = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Spiel')
            ->findOneBy(array('id' => $beginnID));

        $freispiel = $this->erstelleFreispiel();
        $freispiel
            ->setSpielfeld($spiel->getSpielfeld())
            ->setTurniertag($spiel->getTurniertag())
            ->setUhrzeit($spiel->getUhrzeit());

        $em->persist($freispiel);

        if($spielplan->getSpielfeldBelegung() == 1)
        {
            $spiele = $this->getDoctrine()
                ->getRepository('TurnierplanBundle:Spiel')
                ->findBy(array(),array('spielnummer' => 'ASC'));

            $key = array_search($spiel,$spiele);
        }
        else if($spielplan->getSpielfeldBelegung() == 2)
        {
            $spiele = $this->getDoctrine()
                ->getRepository('TurnierplanBundle:Spiel')
                ->findBy(array('spielfeld' => $spiel->getSpielfeld()),array('spielnummer' => 'ASC'));

            $key = array_search($spiel,$spiele);
        }

        while(isset($spiele[$key+1])){

            $spieleTemp = clone $spiele[$key+1]->getUhrzeit();

             $spiele[$key]
                ->setSpielfeld($spiele[$key+1]->getSpielfeld())
                ->setUhrzeit($spieleTemp)
                ->setTurniertag($spiele[$key+1]->getTurniertag());

            $em->persist($spiele[$key]);
            $key++;

        }

        //Letztes Spiel
        if($spielplan->getSpielfeldBelegung() == 1) //keine Spielfeldtreue
        {
        }
        else if($spielplan->getSpielfeldBelegung() == 2) //Spielfeldtreue
        {
            $letzterTurniertag = $this->getDoctrine()
                ->getRepository('TurnierplanBundle:Turniertag')
                ->findOneBy(array('id' => $spiele[count($spiele)-2]->getTurniertag()->getId()));

            $alleTurnierTage = $this->getDoctrine()
                ->getRepository('TurnierplanBundle:Turniertag')
                ->findAll();

            $key = array_search($letzterTurniertag,$alleTurnierTage);

            $neueSpielzeit = clone $spiele[count($spiele)-1]->getUhrzeit();
            $neueSpielzeit->add(new \DateInterval("PT" . $spielplan->getSpielzeit() . "M"));

            if($neueSpielzeit >= $letzterTurniertag->getUhrzEnde())
            {
                if(!isset($alleTurnierTage[$key+1]))
                    return new Response('No available time left!',Response::HTTP_BAD_REQUEST);
                $spiele[count($spiele)-1]
                    ->setSpielfeld($spiel->getSpielfeld())
                    ->setUhrzeit($alleTurnierTage[$key]->getUhrzBeginn())
                    ->setTurniertag($alleTurnierTage[$key+1]);

                $em->persist($spiele[count($spiele)-1]);
            }
            else{

                $spiele[count($spiele)-1]
                    ->setSpielfeld($spiele[count($spiele)-2]->getSpielfeld())
                    ->setUhrzeit($neueSpielzeit)
                    ->setTurniertag($spiele[count($spiele)-2]->getTurniertag());

                $em->persist($spiele[count($spiele)-1]);


            }
        }

        $em->flush();

        $this->vergebeSpielnummern();



        return $this->render('TurnierplanBundle:SpieleErzeugen:list.html.twig');
    }

    /**
     * @Route("/turnierplan/erstellung/4/pausen/api/delete", name="spPausenAPIDelete")
     */
    public function deleteAction()
    {
        $request = Request::createFromGlobals();
        $beginnID = $request->request->get('spielId');

        $em = $this->getDoctrine()->getEntityManager();

        $spielplan = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Spielplan')
            ->findOneBy(array('id' => 1));

        $spiel = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Spiel')
            ->findOneBy(array('id' => $beginnID));

        if($spielplan->getSpielfeldBelegung() == 1)
        {
            $spiele = $this->getDoctrine()
                ->getRepository('TurnierplanBundle:Spiel')
                ->findBy(array(),array('spielnummer' => 'ASC'));

            $key = array_search($spiel,$spiele);
        }
        else if($spielplan->getSpielfeldBelegung() == 2)
        {
            $spiele = $this->getDoctrine()
                ->getRepository('TurnierplanBundle:Spiel')
                ->findBy(array('spielfeld' => $spiel->getSpielfeld()),array('spielnummer' => 'ASC'));

            $key = array_search($spiel,$spiele);
        }

        $beginn = count($spiele)-1;

        while($key != $beginn){

            $spieleTemp = clone $spiele[$beginn-1]->getUhrzeit();

            $spiele[$beginn]
                ->setSpielfeld($spiele[$beginn-1]->getSpielfeld())
                ->setUhrzeit($spieleTemp)
                ->setTurniertag($spiele[$beginn-1]->getTurniertag());

            $em->persist($spiele[$beginn]);
            $beginn--;
        }

        $em->remove($spiel);
        $em->flush();

        $this->vergebeSpielnummern();

        return new Response();
    }

    /**
     * @Route("/turnierplan/erstellung/4/spiele/api/switch", name="spSpieleAPISwitch")
     */
    public function switchAction()
    {
        $request = Request::createFromGlobals();
        $draggedID = $request->request->get('draggedGameId');
        $droppedID = $request->request->get('droppedGameId');

        $spielDragged = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Spiel')
            ->findOneBy(array('id' => $draggedID));

        $spielDropped = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Spiel')
            ->findOneBy(array('id' => $droppedID));

        $em = $this->getDoctrine()->getEntityManager();

        $spielplan = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Spielplan')
            ->findOneBy(array('id' => 1));

        if($spielplan->getSpielfeldBelegung() == 1)
        {
            $spiele = $this->getDoctrine()
                ->getRepository('TurnierplanBundle:Spiel')
                ->findBy(array(),array('spielnummer' => 'ASC'));

            $keyDragged = array_search($spielDragged,$spiele);
            $keyDropped = array_search($spielDropped,$spiele);
        }
        else if($spielplan->getSpielfeldBelegung() == 2) {
            if($spielDragged->getSpielfeld() == $spielDropped->getSpielfeld())
            {
                $spiele = $this->getDoctrine()
                    ->getRepository('TurnierplanBundle:Spiel')
                    ->findBy(array('spielfeld' => $spielDragged->getSpielfeld()),array('spielnummer' => 'ASC'));
            }
            else
            {
                $spielDroppedClone = clone $spielDropped;

                $spielDropped = $this->getDoctrine()
                    ->getRepository('TurnierplanBundle:Spiel')
                    ->findOneBy(array('spielfeld' => $spielDragged->getSpielfeld(), 'uhrzeit' => $spielDropped->getUhrzeit()),array('spielnummer' => 'ASC'));

                if($spielDropped == null) {
                    $spielDragged
                        ->setSpielfeld($spielDroppedClone->getSpielfeld())
                        ->setUhrzeit($spielDroppedClone->getUhrzeit())
                        ->setTurniertag($spielDroppedClone->getTurniertag());

                    $em->persist($spielDragged);
                    $em->flush();

                    return new Response();
                }
                else{
                    $spiele = $this->getDoctrine()
                        ->getRepository('TurnierplanBundle:Spiel')
                        ->findBy(array('spielfeld' => $spielDragged->getSpielfeld()),array('spielnummer' => 'ASC'));
                }
            }
            $keyDragged = array_search($spielDragged,$spiele);
            $keyDropped = array_search($spielDropped,$spiele);
        }

        $spielDroppedCloned = clone $spielDropped;

        if($spielDragged->getSpielnummer() < $spielDropped->getSpielnummer()) {

            $i = $keyDragged + 1;

            while ($i <= $keyDropped) {
                $uhrzeitTemp = clone $spiele[$i - 1]->getUhrzeit();

                $spiele[$i]
                    ->setSpielfeld($spiele[$i - 1]->getSpielfeld())
                    ->setUhrzeit($uhrzeitTemp)
                    ->setTurniertag($spiele[$i - 1]->getTurniertag());

                $em->persist($spiele[$i]);
                $i++;
            }
        }
        else if ($spielDragged->getSpielnummer() > $spielDropped->getSpielnummer())
        {
            $i = $keyDragged - 1;

            while ($i <= $keyDropped) {
                $uhrzeitTemp = clone $spiele[$i + 1]->getUhrzeit();

                $spiele[$i]
                    ->setSpielfeld($spiele[$i + 1]->getSpielfeld())
                    ->setUhrzeit($uhrzeitTemp)
                    ->setTurniertag($spiele[$i + 1]->getTurniertag());
                $em->persist($spiele[$i]);
                $i--;
            }
        }

        $spiele[$keyDragged]
            ->setSpielfeld($spielDroppedCloned->getSpielfeld())
            ->setUhrzeit($spielDroppedCloned->getUhrzeit())
            ->setTurniertag($spielDroppedCloned->getTurniertag());

        $em->persist($spiele[$keyDragged]);

        $em->flush();

        $this->vergebeSpielnummern();

        return new Response();
    }

    public function erstelleFreispiel()
    {
        $freispiel = new Spiel();

        $freispiel
            ->setAltersklasse($this->getDoctrine()
                ->getRepository('TurnierplanBundle:Altersklasse')
                ->findOneBy(array('bezeichnung' => 'FS')));

        return $freispiel;
    }


    /**
     * @Route("/turnierplan/erstellung/4/spiele/api", name="spSpieleAPI")
     */
    public function erstelleJSON()
    {
        $spiele = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Spiel')
            ->findBy(array(),array('spielnummer' => 'ASC'));

        foreach($spiele as $spiel){
            if($spiel instanceof Hauptrundenspiel)
            {
                $mannschaft1new = new Mannschaft();
                $mannschaft2new = new Mannschaft();
                $mannschaft1new->setName($spiel->getHeimPlatz().". Gruppe ".$spiel->getHeimGruppe());
                $mannschaft2new->setName($spiel->getGastPlatz().". Gruppe ".$spiel->getGastGruppe());

                $spiel->setMannschaft1($mannschaft1new);
                $spiel->setMannschaft2($mannschaft2new);
            }
            else if($spiel instanceof Endrundenspiel)
            {
                $mannschaft1new = new Mannschaft();
                $mannschaft2new = new Mannschaft();

                if($spiel->getGvFlag() == 1)
                    $gv="Gewinner Spiel";
                else
                    $gv="Verlierer Spiel";

                $mannschaft1new->setName($gv." ".$spiel->getHauptrundenspiel1()->getSpielnummer());
                $mannschaft2new->setName($gv." ".$spiel->getHauptrundenspiel2()->getSpielnummer());

                $spiel->setMannschaft1($mannschaft1new);
                $spiel->setMannschaft2($mannschaft2new);
            }
            else if($spiel->getAltersklasse()->getId() == 8)
            {
                $mannschaft1new = new Mannschaft();
                $mannschaft2new = new Mannschaft();
                $mannschaft1new->setName("Freispiel");
                $mannschaft2new->setName("Freispiel");

                $spiel->setMannschaft1($mannschaft1new);
                $spiel->setMannschaft2($mannschaft2new);
            }
        }

        $json = '{  "draw": 1,
                    "recordsTotal": '.count($spiele).',
                    "recordsFiltered": '.count($spiele).',
                    "data": [';

        for($i = 0; $i < count($spiele); $i++)
        {
            if ($spiele[$i] instanceof Vorrundenspiel) {
                $mannschaft1 = $spiele[$i]->getMannschaft1()->getName();
                $mannschaft2 = $spiele[$i]->getMannschaft2()->getName();
            } else if ($spiele[$i] instanceof Hauptrundenspiel) {
                $mannschaft1 = $spiele[$i]->getHeimPlatz() . ". Gruppe " . $spiele[$i]->getHeimGruppe();
                $mannschaft2 = $spiele[$i]->getGastPlatz() . ". Gruppe " . $spiele[$i]->getGastGruppe();

            } else if ($spiele[$i] instanceof Endrundenspiel) {
                    if ($spiele[$i]->getGvFlag() == 1)
                        $gv = "Gewinner Spiel";
                    else
                        $gv = "Verlierer Spiel";

                    $mannschaft1 = $gv . " " . $spiele[$i]->getHauptrundenspiel1()->getSpielnummer();
                    $mannschaft2 = $gv . " " . $spiele[$i]->getHauptrundenspiel2()->getSpielnummer();
            }

            $json .= '{ "DT_RowId": "'.$spiele[$i]->getId().'",
                        "uhrzeit": "'.$spiele[$i]->getUhrzeit()->format('d.m.Y H:i').'",
                        "platz": "'.$spiele[$i]->getSpielfeld().'",
                        "jugend": "'.$spiele[$i]->getAltersklasse()->getBezeichnung().'",
                        "heim": "'.$mannschaft1.'",
                        "gast": "'.$mannschaft2.'",
                        "freispiel": "<button class=\"btn btn-default AddDaten\" type=\"button\"><i class=\"glyphicon glyphicon-plus-sign\"></i></button>';

                        if($spiele[$i]->getAltersklasse()->getBezeichnung() == "FS")
                            $json .= '<button class=\"btn btn-default RemoveDaten\" type=\"button\"><i class=\"glyphicon glyphicon-minus-sign\"></i></button>" }';
                        else
                            $json .= '" }';

            if($i+1 != count($spiele))
                $json .= ',';
        }

        return new Response($json.' ] }');
    }

    private function vergebeSpielnummern()
    {
        $spiele = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Spiel')
            ->findBy(array(), array('uhrzeit' => 'ASC', 'spielfeld' => 'ASC'));

        $em = $this->getDoctrine()->getManager();
        $spielnummer = 1;

        foreach ($spiele as $spiel){
            $spiel->setSpielnummer($spielnummer++);
            $em->persist($spiel);
        }

        $em->flush();
    }
}