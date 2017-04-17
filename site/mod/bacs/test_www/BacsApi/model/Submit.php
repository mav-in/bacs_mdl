<?php
/**
 * Created by PhpStorm.
 * User: Wat
 * Date: 29.02.2016
 * Time: 15:03
 */

namespace Bacs\model;


class Submit
{
    private $ProblemId;
    private $Source;
    private $LanguageId;

    /**
     * Submit constructor.
     * @param $ProblemId
     * @param $Source
     * @param $LanguageId
     */
    public function __construct($ProblemId, $Source, $LanguageId)
    {
        $this->ProblemId = $ProblemId;
        $this->Source = $Source;
        $this->LanguageId = $LanguageId;
    }

    /**
     * @return mixed
     */
    public function getProblemId()
    {
        return $this->ProblemId;
    }

    /**
     * @param mixed $ProblemId
     */
    public function setProblemId($ProblemId)
    {
        $this->ProblemId = $ProblemId;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->Source;
    }

    /**
     * @param mixed $Source
     */
    public function setSource($Source)
    {
        $this->Source = $Source;
    }

    /**
     * @return mixed
     */
    public function getLanguageId()
    {
        return $this->LanguageId;
    }

    /**
     * @param mixed $LanguageId
     */
    public function setLanguageId($LanguageId)
    {
        $this->LanguageId = $LanguageId;
    }

}
