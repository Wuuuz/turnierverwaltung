<?php

namespace TurnierplanBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Validator\Constraints\DateTime;
use TeilnehmerBundle\Entity\Mannschaft;

class TurnierplanerstellungTestController extends Controller
{
    /**
     * @Route("/generate/test")
     */
    public function indexAction()
    {
        //Holt alle verfuegbaren Altersklassen
        $altersklasse = $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Altersklasse')
            ->findOneBy(array('id' => 3));

        $em = $this->getDoctrine()->getManager();

        for($i = 1; $i < 7; $i++)
        {
            $mannschaft = new Mannschaft();
            $mannschaft->setAltersklasse($altersklasse);
            $mannschaft->setAnkunft(new \DateTime('2000-01-01'));
            $mannschaft->setMvEmail('testemail');
            $mannschaft->setAnzEssen(4);
            $mannschaft->setAnzSr(2);
            $mannschaft->setMvLand('DE');
            $mannschaft->setMvName('Testname');
            $mannschaft->setStarke(rand(1,100));
            $mannschaft->setMvOrt('Testort');
            $mannschaft->setMvPLZ(23423);
            $mannschaft->setMvVorname('dfasdf');
            $mannschaft->setMvTelefon(234234);
            $mannschaft->setName('mJC'.$i);

            $em->persist($mannschaft);
        }

        $em->flush();

        return $this->render('TurnierplanBundle:Default:index.html.twig');
    }
}
