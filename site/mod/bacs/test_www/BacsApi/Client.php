<?php
/**
 * Created by PhpStorm.
 * User: Wat
 * Date: 02.03.2016
 * Time: 16:53
 */

namespace Bacs;
use Bacs\model as Model;
include('httpful.phar');
include ('model/Language.php');
include ('model/Problem.php');
include ('model/Submit.php');
include ('model/SubmitResult.php');
include ('model/TestResult.php');

class Client
{

    const HOST = 'dc.wincs.cs.istu.ru:83';
#    const HOST = '172.16.128.131:83';
    const CONTEST_ID = 17;
    const AUTHOR = 'sarum9in@gmail.com';

    static public function rejudgeAllSubmit($submitIdAll){
        $uri = '/api/Submit/RejudgeAll';
        $entry = array();
        foreach($submitIdAll as $submitId){
            $entry[] = array('Value'=>$submitId);
        }

        $response = \Httpful\Request::post(self::HOST .$uri)
            ->sendsJson()
            ->body(json_encode(array('Entry'=>$entry)))
            ->send();
        $status = $response->body->Result->Status;
        return ($status == 0) ? true : false;
    }

    static public function rejudgeSubmit($submitId){
        $uri = '/api/Submit/Rejudge';

        $body = array('Value'=>$submitId);

        $response = \Httpful\Request::post(self::HOST .$uri)
            ->sendsJson()
            ->body(json_encode($body))
            ->send();
        $status = $response->body->Result->Status;
        return ($status == 0) ? true : false;
    }

    /**
     * @param $submitIdAll[]
     * @return Model\SubmitResult[]
     * @throws \Httpful\Exception\ConnectionErrorException
     */
    static public function getResultAll($submitIdAll){
        $uri = '/api/Submit/FetchLatestResultAll';

        $params = "?";
        for($i=0; $i<count($submitIdAll); $i++){
            $params = $params . "Entry[$i].Value=" . $submitIdAll[$i] . "&";
        }

        $response = \Httpful\Request::get(self::HOST . $uri . $params)->send();

        $resultAll = array();
        foreach($response->body->Data->Entry as $submitResult){
            $modelSubmitResult = new model\SubmitResult(
                $submitResult->Id->Submit->Value,
                $submitResult->System->Status,
                $submitResult->Build->Status,
                $submitResult->Build->Output
            );
            if(isset($submitResult->TestGroup[0])){
                $testGroup = array  ();
                foreach($submitResult->TestGroup[0]->Test as $testResult){
                    $modelTestResult = new model\TestResult(
                        $testResult->Id,
                        $testResult->Status,
                        $testResult->JudgeMessage,
                        $testResult->ResourceUsage->TimeUsageMillis,
                        $testResult->ResourceUsage->MemoryUsageBytes
                    );
                    $testGroup[$modelTestResult->getTestId()] = $modelTestResult;
                }

                $modelSubmitResult->setTestGroup($testGroup);
            }

            $resultAll[] = $modelSubmitResult;
        }

        return $resultAll;
    }

    /**
     * @param $submitAll[] Model\Submit
     * @return array SubmitId
     */
    static public function sendSubmitAll($submitAll){

        $uri = '/api/Submit/SendAll';

        $entry = array();
        for($i=0;$i<count($submitAll);$i++){
            $submit = $submitAll[$i];
            $body = array();
            $body['Author'] = array('UserLogin'=>self::AUTHOR);
            $body['Problem'] = array('Value'=>$submit->getProblemId(), 'Contest'=>array('Value'=>self::CONTEST_ID));
            $body['Source'] = array(
                'Language'=> array('Value'=>$submit->getLanguageId()),
                'Data' => base64_encode($submit->getSource())
            );
            $entry[] = $body;
        }

        $response = \Httpful\Request::post(self::HOST .$uri)
            ->sendsJson()
            ->body(json_encode(array("Entry"=>$entry)))
            ->send();

        $result = $response->body->Data->Entry;
        $resultSubmitId = array();
        for($i=0;$i<count($result);$i++){
            $resultSubmitId[] = $result[$i]->Submit->Value;
        }
        return $resultSubmitId;

    }

    /**
     * @param $submitId
     * @return Model\SubmitResult
     * @throws \Httpful\Exception\ConnectionErrorException
     */
    static public function getResult($submitId){
        $uri = '/api/Submit/FetchLatestResult';
        $params = "?Value=" . $submitId;

        $response = \Httpful\Request::get(self::HOST . $uri . $params)->send();

        $submitResult = $response->body->Data;
        $modelSubmitResult = new model\SubmitResult(
            $submitResult->Id->Submit->Value,
            $submitResult->System->Status,
            $submitResult->Build->Status,
            $submitResult->Build->Output
        );
        if(isset($submitResult->TestGroup[0])){
            $testGroup = array  ();
            foreach($submitResult->TestGroup[0]->Test as $testResult){
                $modelTestResult = new model\TestResult(
                    $testResult->Id,
                    $testResult->Status,
                    $testResult->JudgeMessage,
                    $testResult->ResourceUsage->TimeUsageMillis,
                    $testResult->ResourceUsage->MemoryUsageBytes
                );
                $testGroup[$modelTestResult->getTestId()] = $modelTestResult;
            }
            $modelSubmitResult->setTestGroup($testGroup);
        }

        return $modelSubmitResult;
    }

    /**
     * @param $submit
     * @return mixed SubmitId
     */
    static public function sendSubmit($submit){
        $uri = '/api/Submit/Send';
        $body = array();
        $body['Author'] = array('UserLogin'=>self::AUTHOR);
        $body['Problem'] = array('Value'=>$submit->getProblemId(), 'Contest'=>array('Value'=>self::CONTEST_ID));
        $body['Source'] = array(
            'Language'=> array('Value'=>$submit->getLanguageId()),
            'Data' => base64_encode($submit->getSource())
        );

        $response = \Httpful\Request::post(self::HOST .$uri)
            ->sendsJson()
            ->body(json_encode($body))
            ->send();

        return $response->body->Data->Submit->Value;
    }

    /**
     * @return Problem[]
     * @throws \Httpful\Exception\ConnectionErrorException
     */
    static public function getProblems(){
        $uri = '/api/Contest/GetContest';
        $params = "?Value=" . self::CONTEST_ID;

        $allProblem = array();
        $response = \Httpful\Request::get(self::HOST . $uri . $params)->send();
        foreach($response->body->Data->Problems as $problem){
            $id = $problem->Id->Value;
            $info = json_decode(json_encode($problem->Info), true)['C'];
            $timeLimitMillis = $problem->ResourceLimits->TimeLimitMillis;
            $memoryLimitBytes = $problem->ResourceLimits->MemoryLimitBytes;

            $modelProblem = new model\Problem($id, $info, $timeLimitMillis, $memoryLimitBytes);
            $allProblem[] = $modelProblem;
        }

        return $allProblem;
    }


    /**
     * @param $problemId
     * @return string
     * @throws \Httpful\Exception\ConnectionErrorException
     */
    static public function getStatementUrl($problemId){
        $uri = '/api/Statement/GetStatementUrl';
        $params = "?Problem.value=". urlencode($problemId) . "&Problem.Contest.value=" . self::CONTEST_ID;

        $response = \Httpful\Request::get(self::HOST . $uri . $params)->send();

        return $response->body->Data->Value;
    }

    /**
     * @return Language[]
     * @throws \Httpful\Exception\ConnectionErrorException
     */
    static public function getAllLanguages() : array {
        $uri = '/api/Language/GetAllLanguages';
        $allLanguage = array();

        $response = \Httpful\Request::get(self::HOST . $uri)->send();
        foreach($response->body->Data->Entry as $item){
            $id = $item->Id->Value;
            $info = array();
            $allInfo = json_decode(json_encode($item->Info), true);
            foreach($allInfo as $inf){
                $info[$inf['Name']] = $inf['Description'];
            }
            $timeLimitMillis = $item->ResourceLimits->TimeLimitMillis;
            $memoryLimitBytes = $item->ResourceLimits->MemoryLimitBytes;
            $compilerType = $item->CompilerType->Name;

            $modelLanguage = new Model\Language(
                $id,
                $info,
                $timeLimitMillis,
                $memoryLimitBytes,
                $compilerType
            );

            $allLanguage[] = $modelLanguage;
        }
        return $allLanguage;
    }

    /**
     * @return bool
     * @throws \Httpful\Exception\ConnectionErrorException
     */
    static public function ping(){
        $uri = '/api/Contest/Ping';

        $response = \Httpful\Request::get(self::HOST . $uri)->send();
        if($response->body->Data === 'Herly Nado')
            return true;

        return false;
    }
}