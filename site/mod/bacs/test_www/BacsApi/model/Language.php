<?php
/**
 * Created by PhpStorm.
 * User: Wat
 * Date: 02.03.2016
 * Time: 17:30
 */

namespace Bacs\model;


class Language
{
    private $Id;
    /**
     * @var array Array has format 'Name'=>'Description'
     */
    private $Info;
    private $TimeLimitMillis;
    private $MemoryLimitBytes;
    private $CompilerType;

    /**
     * Language constructor.
     * @param $Id
     * @param $Info
     * @param $TimeLimitMillis
     * @param $MemoryLimitBytes
     * @param $CompilerType
     */
    public function __construct($Id, $Info, $TimeLimitMillis, $MemoryLimitBytes, $CompilerType)
    {
        $this->Id = $Id;
        $this->Info = $Info;
        $this->TimeLimitMillis = $TimeLimitMillis;
        $this->MemoryLimitBytes = $MemoryLimitBytes;
        $this->CompilerType = $CompilerType;
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

    /**
     * @return mixed
     */
    public function getCompilerType()
    {
        return $this->CompilerType;
    }

    /**
     * @param mixed $CompilerType
     */
    public function setCompilerType($CompilerType)
    {
        $this->CompilerType = $CompilerType;
    }



}