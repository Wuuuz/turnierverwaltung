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
use TeilnehmerBundle\Entity\Mannschaft;
use TeilnehmerBundle\Utils\AltersklasseAnmeldungWrapper;
use TurnierplanBundle\Entity\AltersklassenGruppenWrapper;

class GruppenEinteilenController extends Controller
{
    /**
     * @Route("/turnierplan/erstellung/2", name="spGruppenEinteilen")
     */
    public function listAction()
    {
        $this->gruppenEinteilenAction();

        $alterklasseWrapperArray = array();

        $altersklassen = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Altersklasse')
            ->findAll();

        foreach($altersklassen as $altersklasse)
        {
            $altersklasseWrapper = new \TurnierplanBundle\Utils\AltersklassenGruppenWrapper();
            $altersklasseWrapper->setAltersklasse($altersklasse);

            $mannschaften = $this->getDoctrine()
                ->getRepository('TeilnehmerBundle:Mannschaft')
                ->findBy(array('altersklasse' => $altersklasse, 'gruppe' => 1));

            if(count($mannschaften) > 0)
                $altersklasseWrapper->setMannschaftenG1($mannschaften);

            $mannschaften = $this->getDoctrine()
                ->getRepository('TeilnehmerBundle:Mannschaft')
                ->findBy(array('altersklasse' => $altersklasse, 'gruppe' => 2));

            if(count($mannschaften) > 0)
                $altersklasseWrapper->setMannschaftenG2($mannschaften);


            array_push($alterklasseWrapperArray,$altersklasseWrapper);

        }

        return $this->render('TurnierplanBundle:GruppenEinteilen:list.html.twig', array(
            'altersklassen' => $alterklasseWrapperArray
        ));
    }

    /**
     * @Route("/turnierplan/erstellung/2/aendern/{mannschaftID}/{gruppe}", name="spGruppenEinteilenAendern")
     */
    public function gruppeAendernAPIAction($mannschaftID,$gruppe)
    {
        $mannschaft = $this->getDoctrine()
            ->getRepository('TeilnehmerBundle:Mannschaft')
            ->findOneBy(array('id' => $mannschaftID));

        $mannschaft->setGruppe(substr($gruppe,-1));

        $em = $this->getDoctrine()->getManager();
        $em->persist($mannschaft);
        $em->flush();

        return new Response();
    }

    private function gruppenEinteilenAction()
    {
        //Holt alle verfuegbaren Altersklassen
        $altersklassen = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Altersklasse')
            ->findAll();

        //Holt fuer jede Altersklasse die Mannschaften
        foreach ($altersklassen as $altersklasse) {
            $mannschaften = $this->getDoctrine()
                ->getRepository('TeilnehmerBundle:Mannschaft')
                ->findBy(array('altersklasse' => $altersklasse), array('starke' => 'DESC'));

            //Falls mehr als 7 Mannschaften -> zwei Gruppen
            if (count($mannschaften) > 7)
                $gruppenAnzahl = 2;
            else
                $gruppenAnzahl = 1;

            $em = $this->getDoctrine()->getManager();

            //Gruppeneinteilung nach Staerke
            for ($i = 0; $i < count($mannschaften); $i++) {
                $mannschaften[$i]->setGruppe($i % $gruppenAnzahl + 1);
                $em->persist($mannschaften[$i]);
            }

            // actually executes the queries (i.e. the INSERT query)
            $em->flush();
        }
    }
}