<?php
/**
 * Created by PhpStorm.
 * User: Andreas
 * Date: 28.12.2016
 * Time: 19:52
 */

namespace TurnierplanBundle\Utils;


class MannschaftTabelle
{
    private $mannschaft;

    private $spiele;
    private $torePlus;
    private $toreMinus;
    private $siege;
    private $unentschieden;
    private $niederlagen;
    private $punktePlus;
    private $punkteMinus;

    /**
     * @return mixed
     */
    public function getSpiele()
    {
        return $this->spiele;
    }

    /**
     * @param mixed $spiele
     */
    public function setSpiele($spiele)
    {
        $this->spiele = $spiele;
    }

    public function __construct($mannschaft)
    {
        $this->mannschaft = $mannschaft;
    }

    /**
     * @return mixed
     */
    public function getMannschaft()
    {
        return $this->mannschaft;
    }

    /**
     * @param mixed $mannschaft
     */
    public function setMannschaft($mannschaft)
    {
        $this->mannschaft = $mannschaft;
    }

    /**
     * @return mixed
     */
    public function getTorePlus()
    {
        return $this->torePlus;
    }

    /**
     * @param mixed $torePlus
     */
    public function setTorePlus($torePlus)
    {
        $this->torePlus = $torePlus;
    }

    /**
     * @return mixed
     */
    public function getToreMinus()
    {
        return $this->toreMinus;
    }

    /**
     * @param mixed $toreMinus
     */
    public function setToreMinus($toreMinus)
    {
        $this->toreMinus = $toreMinus;
    }

    /**
     * @return mixed
     */
    public function getSiege()
    {
        return $this->siege;
    }

    /**
     * @param mixed $siege
     */
    public function setSiege($siege)
    {
        $this->siege = $siege;
    }

    /**
     * @return mixed
     */
    public function getUnentschieden()
    {
        return $this->unentschieden;
    }

    /**
     * @param mixed $unentschieden
     */
    public function setUnentschieden($unentschieden)
    {
        $this->unentschieden = $unentschieden;
    }

    /**
     * @return mixed
     */
    public function getNiederlagen()
    {
        return $this->niederlagen;
    }

    /**
     * @param mixed $niederlagen
     */
    public function setNiederlagen($niederlagen)
    {
        $this->niederlagen = $niederlagen;
    }

    /**
     * @return mixed
     */
    public function getPunktePlus()
    {
        return $this->punktePlus;
    }

    /**
     * @param mixed $punktePlus
     */
    public function setPunktePlus($punktePlus)
    {
        $this->punktePlus = $punktePlus;
    }

    /**
     * @return mixed
     */
    public function getPunkteMinus()
    {
        return $this->punkteMinus;
    }

    /**
     * @param mixed $punkteMinus
     */
    public function setPunkteMinus($punkteMinus)
    {
        $this->punkteMinus = $punkteMinus;
    }

    public function erhoeheSpiele()
    {
        $this->spiele++;
    }

    public function erhoeheTore($plus,$minus)
    {
        $this->toreMinus += $minus;
        $this->torePlus += $plus;

    }

    public function schreibeGewinn()
    {
        $this->siege++;
    }

    public function schreibeNiederlage()
    {
        $this->niederlagen++;
    }

    public function schreibeUnentschieden()
    {
        $this->unentschieden++;
    }
}