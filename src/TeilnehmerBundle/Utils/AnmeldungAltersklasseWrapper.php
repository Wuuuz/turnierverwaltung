<?php

/**
 * Created by PhpStorm.
 * User: Andreas
 * Date: 04.12.2016
 * Time: 21:52
 */

namespace TeilnehmerBundle\Utils;

class AnmeldungAltersklasseWrapper
{
    private $alterklasseBez;
    private $id;
    private $mannschaftWrapper1;
    private $mannschaftWrapper2;

    /**
     * @return mixed
     */
    public function getAlterklasseBez()
    {
        return $this->alterklasseBez;
    }

    /**
     * @param mixed $alterklasseBez
     */
    public function setAlterklasseBez($alterklasseBez)
    {
        $this->alterklasseBez = $alterklasseBez;
    }

    /**
     * @return mixed
     */
    public function getMannschaftWrapper1()
    {
        return $this->mannschaftWrapper1;
    }

    /**
     * @param mixed $mannschaftWrapper1
     */
    public function setMannschaftWrapper1($mannschaftWrapper1)
    {
        $this->mannschaftWrapper1 = $mannschaftWrapper1;
    }

    /**
     * @return mixed
     */
    public function getMannschaftWrapper2()
    {
        return $this->mannschaftWrapper2;
    }

    /**
     * @param mixed $mannschaftWrapper2
     */
    public function setMannschaftWrapper2($mannschaftWrapper2)
    {
        $this->mannschaftWrapper2 = $mannschaftWrapper2;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }



}