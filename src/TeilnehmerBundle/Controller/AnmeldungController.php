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

        $jugenden = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Jugend')
            ->findAll();

        $counter = 0;

        foreach($jugenden as $jugend)
        {
            $altersklassen = $this->getDoctrine()
                ->getRepository('TurnierplanBundle:Altersklasse')
                ->findBy(array('jugend' => $jugend), array('geschlecht' => 'ASC'));

            $alterklasseWrapper = new AnmeldungAltersklasseWrapper();
            $alterklasseWrapper->setJugendBez($jugend->getBezeichnung());
            $alterklasseWrapper->setId($counter++);

            foreach($altersklassen as $altersklasse)
            {
                $mannschaften = $this->getDoctrine()
                    ->getRepository('TeilnehmerBundle:Mannschaft')
                    ->findMannschaftByStatusCompare(1,$altersklasse);

                if(count($mannschaften) > 0)
                {
                    $mannschaftsWrapper = new AnmeldungMannschaftWrapper();
                    $mannschaftsWrapper->setGeschlechtBez($this->mapGeschlecht($altersklasse->getGeschlecht()));
                    $mannschaftsWrapper->setMannschaften($mannschaften);

                    if($alterklasseWrapper->getMannschaftWrapper1() == null)
                        $alterklasseWrapper->setMannschaftWrapper1($mannschaftsWrapper);
                    elseif ($alterklasseWrapper->getMannschaftWrapper2() == null)
                        $alterklasseWrapper->setMannschaftWrapper1($mannschaftsWrapper);
                    else{
                        $alterklasseWrapper2 = new AnmeldungAltersklasseWrapper();
                        $alterklasseWrapper2->setJugendBez($jugend->getBezeichnung());
                    }
                }
            }
            if($alterklasseWrapper->getMannschaftWrapper1() != null) {
                array_push($alterklasseWrapperArray, $alterklasseWrapper);
                if($alterklasseWrapper->getMannschaftWrapper2() != null)
                    array_push($alterklasseWrapperArray, $alterklasseWrapper2);
            }

        }

        return $this->render('TeilnehmerBundle:Anmeldung:list.html.twig', array(
            'altersklassen' => $alterklasseWrapperArray
        ));
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
