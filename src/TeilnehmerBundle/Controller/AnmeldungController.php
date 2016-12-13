<?php

namespace TeilnehmerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use TeilnehmerBundle\Entity\Mannschaft;
use TeilnehmerBundle\Utils\AltersklasseAnmeldungWrapper;
use TeilnehmerBundle\Utils\AnmeldungAltersklasseWrapper;
use TeilnehmerBundle\Utils\AnmeldungMannschaftWrapper;

class AnmeldungController extends Controller
{
    /**
     * @Route("/anmeldung", name="anmeldungList")
     */
    public function listAction()
    {
        $alterklasseWrapperArray = array();

        $altersklassen = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Altersklasse')
            ->findBy(array(), array('alter' => 'ASC', 'geschlecht' => 'ASC'));

        for($i = 0; $i < count($altersklassen); $i++)
        {
            $alterklasseWrapper = new AnmeldungAltersklasseWrapper();
            $alterklasseWrapper->setAlterklasseBez($this->mapJugend($altersklassen[$i]->getAlter()));
            $alterklasseWrapper->setId($i);

            $mannschaften = $this->getDoctrine()
                ->getRepository('TeilnehmerBundle:Mannschaft')
                ->findMannschaftByStatusCompare(1,$altersklassen[$i]);

            if(count($mannschaften) > 0){
                $mannschaftsWrapper = new AnmeldungMannschaftWrapper();
                $mannschaftsWrapper->setGeschlechtBez($this->mapGeschlecht($altersklassen[$i]->getGeschlecht()));
                $mannschaftsWrapper->setMannschaften($mannschaften);
                $alterklasseWrapper->setMannschaftWrapper1($mannschaftsWrapper);
            }

            if(isset($altersklassen[$i+1]))
                if($altersklassen[$i+1]->getAlter() == $altersklassen[$i]->getAlter()){
                    $mannschaften = $this->getDoctrine()
                        ->getRepository('TeilnehmerBundle:Mannschaft')
                        ->findMannschaftByStatusCompare(1,$altersklassen[$i+1]);

                    if(count($mannschaften) > 0){
                        $mannschaftsWrapper = new AnmeldungMannschaftWrapper();
                        $mannschaftsWrapper->setGeschlechtBez($this->mapGeschlecht($altersklassen[$i+1]->getGeschlecht()));
                        $mannschaftsWrapper->setMannschaften($mannschaften);
                        $alterklasseWrapper->setMannschaftWrapper2($mannschaftsWrapper);
                    }
                    $i++;
                }
            array_push($alterklasseWrapperArray,$alterklasseWrapper);

        }

        return $this->render('TeilnehmerBundle:Anmeldung:list.html.twig', array(
            'altersklassen' => $alterklasseWrapperArray
        ));
    }

    private function mapJugend($jugend)
    {
        switch($jugend) {
            case '1':
                return "Jugend D";
            case '2':
                return "Jugend C";
            case '3':
                return "Jugend B";
            case '4':
                return "Jugend A";
            case '5':
                return "Senioren";
        }
    }

    private function mapGeschlecht($geschlecht){
        switch($geschlecht)
        {
            case '1':
                return "männlich";
            case '2':
                return "weiblich";
            case '3':
                return "gemischt";
        }
    }
}
