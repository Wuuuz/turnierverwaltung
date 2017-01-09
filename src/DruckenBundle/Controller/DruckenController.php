<?php

namespace DruckenBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use TurnierplanBundle\Entity\Spiel;

class DruckenController extends Controller
{
    /**
     * @Route("/drucken")
     */
    public function indexAction()
    {
        return $this->render('DruckenBundle:Drucken:list.html.twig');
    }



    public function erzeugePDF()
    {
        $request = Request::createFromGlobals();

        $index = $request->request->get('auswahlArray');

        $spiele =  $this->getDoctrine()
            ->getRepository('TurnierplanBundle:Spiel')
            ->findAll();

        $turnier = $this->getDoctrine()
            ->getRepository('AppBundle:Turnier')
            ->findOneBy(array('id' => 1));

        //PDF wird erzeugt
        $pdf = new FPDI();
        $pdf->AddPage();

        //einzelne Werte werden in PDF uebernommen
        $pdf->SetFont('Arial','B');
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetXY(30, 3);
        $pdf->MultiCell(150,5, $turnier->getName(),0,'C',0);


        //Kopf drucken
        $xHead = 5;
        foreach($index as $value)
            $xHead = $this->printHead($value,$xHead);

        //Spiele drucken
        $yGame = 15;
        foreach ($spiele as $spiel)
            $this->printSpiel($index, $spiel, $yGame);

        $pdf->Output('Turnierdruck.pdf','I');
    }

    function printSpiel($index,$spiel,$yGame)
    {
        $x = 5;
        $yHead = 11;
        $y = $yGame;

        for ($i = 0; $i < count($index); $i++) {
            switch ($index[$i]) {
                case 0:
                    $tableContent = $spiel->getSpielnummer();
                    break;
                case 1:
                    $tableContent = $spiel->getMannschaft1();
                    break;
                case 2:
                    $tableContent = $spiel->getMannschaft2();
                    break;
                case 3:
                    $tableContent = $spiel->getSchiedsrichter1();
                    break;
                case 4:
                    $tableContent = $spiel->getAltersklasse()->getBezeichnung();
                    break;
                case 5:
                    $tableContent = $spiel->getUhrzeit()->format("H:i");
                    break;
                case 6:
                    $tableContent = $spiel->getSpielfeld();
                    break;
            }

            $pdf->SetFont('Arial', '', 8);
            $pdf->SetXY($x, $y);

            $width = 38;
            if ($index == 0 || $index == 4 || $index == 6)
                $width = 16;

            $pdf->MultiCell($width, 4, $tableContent, 1, 'C', 0);

            $x += $width;
        }
        return $yGame+4;
    }

    function printHead ($index,$xHead)
    {
        switch($index) {
            case 0:
                $tableName = "SpNr";
                break;
            case 1:
                $tableName = "Heimmannschaft";
                break;
            case 2:
                $tableName = "Gastmannschaft";
                break;
            case 3:
                $tableName = "Schiedsrichter";
                break;
            case 4:
                $tableName = "Jugend";
                break;
            case 5:
                $tableName = "Uhrzeit";
                break;
            case 6:
                $tableName = "Spielfeld";
                break;
            }

        $width = 38;
        if ($index == 0 || $index == 4 || $index == 6)
            $width = 16;

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY($xHead, 11);
        $pdf->MultiCell($width, 4, $tableName, 1, 'C', 0);

            return $xHead+$width;
    }
}
