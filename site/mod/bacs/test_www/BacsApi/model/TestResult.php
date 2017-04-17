<?php
/**
 * Created by PhpStorm.
 * User: Wat
 * Date: 17.03.2016
 * Time: 15:12
 */

namespace Bacs\model;


class TestResult
{
    private $TestId;
    private $Status;
    private $JudgeMessage;
    private $TimeUsageMillis;
    private $MemoryUsageBytes;

    /**
     * TestResult constructor.
     * @param $TestId
     * @param $Status
     * @param $JudgeMessage
     * @param $TimeUsageMillis
     * @param $MemoryUsageBytes
     */
    public function __construct($TestId, $Status, $JudgeMessage, $TimeUsageMillis, $MemoryUsageBytes)
    {
        $this->TestId = $TestId;
        $this->Status = $Status;
        $this->JudgeMessage = $JudgeMessage;
        $this->TimeUsageMillis = $TimeUsageMillis;
        $this->MemoryUsageBytes = $MemoryUsageBytes;
    }

    /**
     * @return mixed
     */
    public function getTestId()
    {
        return $this->TestId;
    }

    /**
     * @param mixed $TestId
     */
    public function setTestId($TestId)
    {
        $this->TestId = $TestId;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->Status;
    }

    /**
     * @param mixed $Status
     */
    public function setStatus($Status)
    {
        $this->Status = $Status;
    }

    /**
     * @return mixed
     */
    public function getJudgeMessage()
    {
        return $this->JudgeMessage;
    }

    /**
     * @param mixed $JudgeMessage
     */
    public function setJudgeMessage($JudgeMessage)
    {
        $this->JudgeMessage = $JudgeMessage;
    }

    /**
     * @return mixed
     */
    public function getTimeUsageMillis()
    {
        return $this->TimeUsageMillis;
    }

    /**
     * @param mixed $TimeUsageMillis
     */
    public function setTimeUsageMillis($TimeUsageMillis)
    {
        $this->TimeUsageMillis = $TimeUsageMillis;
    }

    /**
     * @return mixed
     */
    public function getMemoryUsageBytes()
    {
        return $this->MemoryUsageBytes;
    }

    /**
     * @param mixed $MemoryUsageBytes
     */
    public function setMemoryUsageBytes($MemoryUsageBytes)
    {
        $this->MemoryUsageBytes = $MemoryUsageBytes;
    }


}