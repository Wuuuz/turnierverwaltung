<?php
/**
 * Created by PhpStorm.
 * User: Andreas
 * Date: 05.12.2016
 * Time: 21:39
 */

namespace TeilnehmerBundle\Utils;


class AnmeldungMannschaftWrapper
{
    private $geschlechtBez;
    private $mannschaften = array();

    /**
     * @return mixed
     */
    public function getGeschlechtBez()
    {
        return $this->geschlechtBez;
    }

    /**
     * @param mixed $geschlechtBez
     */
    public function setGeschlechtBez($geschlechtBez)
    {
        $this->geschlechtBez = $geschlechtBez;
    }

    /**
     * @return array
     */
    public function getMannschaften()
    {
        return $this->mannschaften;
    }

    /**
     * @param array $mannschaften
     */
    public function setMannschaften($mannschaften)
    {
        $this->mannschaften = array_merge($this->mannschaften,$mannschaften);
    }

}