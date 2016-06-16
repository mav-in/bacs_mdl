<?php
/**
 * Created by PhpStorm.
 * User: Wat
 * Date: 05.03.2016
 * Time: 17:10
 */

namespace Bacs\model;


class Problem
{
    private $Id;
    private $Info;
    private $TimeLimitMillis;
    private $MemoryLimitBytes;

    /**
     * Problem constructor.
     * @param $Id
     * @param $Info
     * @param $TimeLimitMillis
     * @param $MemoryLimitBytes
     */
    public function __construct($Id, $Info, $TimeLimitMillis, $MemoryLimitBytes)
    {
        $this->Id = $Id;
        $this->Info = $Info;
        $this->TimeLimitMillis = $TimeLimitMillis;
        $this->MemoryLimitBytes = $MemoryLimitBytes;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->Id;
    }

    /**
     * @param mixed $Id
     */
    public function setId($Id)
    {
        $this->Id = $Id;
    }

    /**
     * @return mixed
     */
    public function getInfo()
    {
        return $this->Info;
    }

    /**
     * @param mixed $Info
     */
    public function setInfo($Info)
    {
        $this->Info = $Info;
    }

    /**
     * @return mixed
     */
    public function getTimeLimitMillis()
    {
        return $this->TimeLimitMillis;
    }

    /**
     * @param mixed $TimeLimitMillis
     */
    public function setTimeLimitMillis($TimeLimitMillis)
    {
        $this->TimeLimitMillis = $TimeLimitMillis;
    }

    /**
     * @return mixed
     */
    public function getMemoryLimitBytes()
    {
        return $this->MemoryLimitBytes;
    }

    /**
     * @param mixed $MemoryLimitBytes
     */
    public function setMemoryLimitBytes($MemoryLimitBytes)
    {
        $this->MemoryLimitBytes = $MemoryLimitBytes;
    }

}