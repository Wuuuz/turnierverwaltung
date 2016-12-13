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
use TeilnehmerBundle\Entity\Mannschaft;
use TeilnehmerBundle\Utils\AltersklasseAnmeldungWrapper;
use TeilnehmerBundle\Utils\AnmeldungAltersklasseWrapper;
use TeilnehmerBundle\Utils\AnmeldungMannschaftWrapper;

class TurnierspezifikaController extends Controller
{
    /**
     * @Route("/turnierplan/erstellung/3", name="spTurnierspezifika")
     */
    public function listAction()
    {

        return $this->render('TurnierplanBundle:AnmeldungUebersicht:list.html.twig');
    }
}