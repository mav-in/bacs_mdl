<?php

echo "Go!";

use Bacs\model as Model;

include 'BacsApi/Client.php';
$apiClient = new Bacs\Client();

#rejudgeAllSubmit###############################################################

$submitIdAll = array(4012,4052);

try{
    $response = $apiClient->rejudgeAllSubmit($submitIdAll);
    var_dump($response);
}catch(Exception $error){
    echo $error->getMessage();
}

#rejudgeSubmit##################################################################

$submitId = 4051;

try{
    $response = $apiClient->rejudgeSubmit($submitId);
    var_dump($response);
}catch(Exception $error){
    echo $error->getMessage();
}

#getResultAll###################################################################

$submit = new Model\Submit('D','LOL',1);
$all[] = $submit;
$all[] = new Model\Submit('D','tot',2);

try{
    echo '<pre>';
    $ResultAll = $apiClient->getResultAll(array(4051,4052));
    #var_dump($ResultAll);
    echo '</pre>';
}catch(Exception $e){
    echo $e->getMessage();
}

foreach($ResultAll as $key => $obj){
    if (!($obj instanceof Model\SubmitResult)) {
        echo 'Error: сделать исключение';
    }
    $obj->getSubmitId();
    $obj->getSystemStatus();
    $obj->getBuildStatus();
    $obj->getBuildOutput();
    $obj->getTestGroup();
}

#getResult######################################################################

try{
    $obj = $apiClient->getResult(4012);
    var_dump($obj);
}catch(Exception $error){
    echo $error->getMessage();
}

if (!($obj instanceof Model\SubmitResult)) {
    echo 'Error: сделать исключение';
}
echo '</br>getSubmitId: '.$obj->getSubmitId();
echo '</br>getSystemStatus: '.$obj->getSystemStatus();
echo '</br>getBuildStatus: '.$obj->getBuildStatus();
echo '</br>getBuildOutput: '.$obj->getBuildOutput();
echo '</br>getTestGroup: '.$obj->getTestGroup();
/*
#sendSubmitAll##################################################################

$submit = new Bacs\model\Submit('D','LOL',1);
$all[] = $submit;
$all[] = new Bacs\model\Submit('D','tot',2);

try{
    $SubmitAll = $apiClient->sendSubmitAll($all);
    echo var_dump($SubmitAll);
}catch(Exception $e){
    echo $e->getMessage();
}

foreach($SubmitAll as $key => $obj){
    #if (!($obj instanceof Model\Language)) {
    #    echo 'Error: сделать исключение';
    #}
    echo var_dump($obj);
}


#sendSubmit#####################################################################

$submit = new Bacs\model\Submit('D','LOL',1);

try{
    $getSubmit = $apiClient->sendSubmit($submit);
    #var_dump($getSubmit);
}catch(Exception $error){
    echo $error->getMessage();
}
echo $getSubmit;
*/
#getProblems####################################################################

try{
    $Problems = $apiClient->getProblems();
    #var_dump($response);
}catch(Exception $error){
    echo $error->getMessage();
}

foreach($Problems as $key => $obj){
    if (!($obj instanceof Model\Problem)) {
        echo 'Error: сделать исключение';
    }
    $obj->getId();
    foreach($obj->getInfo() as $key => $objInfo){

    }
    $obj->getTimeLimitMillis();
    $obj->getMemoryLimitBytes();
}

#getStatementUrl################################################################

$problemId = 'A';

try{
    $StatementUrl = $apiClient->getStatementUrl($problemId);
    var_dump($StatementUrl);
}catch(Exception $error){
    echo $error->getMessage();
}
echo $getSubmit;


#getAllLanguages################################################################

try{
    $AllLanguages = $apiClient->getAllLanguages();
    var_dump($AllLanguages);
}catch(Exception $error){
    echo $error->getMessage();
}

foreach($AllLanguages as $key => $obj){
    if (!($obj instanceof Model\Language)) {
        echo 'Error: сделать исключение';
    }
    echo '</br>id: '.$obj->getId();
    foreach($obj->getInfo() as $key => $objInfo){
        echo '</br>info: '.$objInfo;
    }
    echo '</br>time: '.$obj->getTimeLimitMillis();
    echo '</br>memory: '.$obj->getMemoryLimitBytes();
    echo '</br>type: '.$obj->getCompilerType();
}


try{
    $response = $apiClient->Ping();
    var_dump($response);
}catch(Exception $error){
    echo $error->getMessage();
}

/*
$submit = new Bacs\model\Submit('D','LOL',1);
$all[] = $submit;
$all[] = new Bacs\model\Submit('D','tot',2);

try{
    echo '<pre>';
    $res = $apiClient->getResultAll(array(4051,4052));
    var_dump($res);
    echo '</pre>';
}catch(Exception $e){
    echo $e->getMessage();
}
  
 */