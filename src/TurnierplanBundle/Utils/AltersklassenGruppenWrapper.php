<?php
/**
 * Created by PhpStorm.
 * User: Andreas
 * Date: 06.12.2016
 * Time: 14:28
 */

namespace TurnierplanBundle\Utils;


class AltersklassenGruppenWrapper
{
    private $altersklasse;
    private $mannschaftenG1 = array();
    private $mannschaftenG2 = array();

    /**
     * @return mixed
     */
    public function getAltersklasse()
    {
        return $this->altersklasse;
    }

    /**
     * @param mixed $alterklasse
     */
    public function setAltersklasse($altersklasse)
    {
        $this->altersklasse = $altersklasse;
    }

    /**
     * @return array
     */
    public function getMannschaftenG1()
    {
        return $this->mannschaftenG1;
    }

    /**
     * @param array $mannschaftenG1
     */
    public function setMannschaftenG1($mannschaftenG1)
    {
        $this->mannschaftenG1 = $mannschaftenG1;
    }

    /**
     * @return array
     */
    public function getMannschaftenG2()
    {
        return $this->mannschaftenG2;
    }

    /**
     * @param array $mannschaftenG2
     */
    public function setMannschaftenG2($mannschaftenG2)
    {
        $this->mannschaftenG2 = $mannschaftenG2;
    }


}