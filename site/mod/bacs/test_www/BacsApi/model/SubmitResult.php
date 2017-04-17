<?php
/**
 * Created by PhpStorm.
 * User: Wat
 * Date: 17.03.2016
 * Time: 15:06
 */

namespace Bacs\model;


/**
 * Class SubmitResult
 * @package Bacs\model
 */
class SubmitResult
{
    private $SubmitId;
    private $SystemStatus;
    private $BuildStatus;
    private $BuildOutput;
    private $TestGroup;

    /**
     * SubmitResult constructor.
     * @param $SubmitId
     * @param $SystemStatus
     * @param $BuildStatus
     * @param $BuildOutput
     * @param $TestGroup[] TestResult
     */
    public function __construct($SubmitId, $SystemStatus, $BuildStatus, $BuildOutput, $TestGroup = null)
    {
        $this->SubmitId = $SubmitId;
        $this->SystemStatus = $SystemStatus;
        $this->BuildStatus = $BuildStatus;
        $this->BuildOutput = $BuildOutput;
        $this->TestGroup = $TestGroup;
    }

    /**
     * @return mixed
     */
    public function getSubmitId()
    {
        return $this->SubmitId;
    }

    /**
     * @param mixed $SubmitId
     */
    public function setSubmitId($SubmitId)
    {
        $this->SubmitId = $SubmitId;
    }

    /**
     * @return mixed
     */
    public function getSystemStatus()
    {
        return $this->SystemStatus;
    }

    /**
     * @param mixed $SystemStatus
     */
    public function setSystemStatus($SystemStatus)
    {
        $this->SystemStatus = $SystemStatus;
    }

    /**
     * @return mixed
     */
    public function getBuildStatus()
    {
        return $this->BuildStatus;
    }

    /**
     * @param mixed $BuildStatus
     */
    public function setBuildStatus($BuildStatus)
    {
        $this->BuildStatus = $BuildStatus;
    }

    /**
     * @return mixed
     */
    public function getBuildOutput()
    {
        return $this->BuildOutput;
    }

    /**
     * @param mixed $BuildOutput
     */
    public function setBuildOutput($BuildOutput)
    {
        $this->BuildOutput = $BuildOutput;
    }

    /**
     * @return mixed
     */
    public function getTestGroup()
    {
        return $this->TestGroup;
    }

    /**
     * @param mixed $TestGroup
     */
    public function setTestGroup($TestGroup)
    {
        $this->TestGroup = $TestGroup;
    }


}