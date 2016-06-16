<?php

include 'Client.php';
$apiClient = new Bacs\Client();

#rejudgeAllSubmit
/*
try{
    $res = $apiClient->rejudgeAllSubmit(array(4053));
    print_r($res);
}catch(Exception $e){
    print_r($e->getMessage());
}
*/

#rejudgeSubmit
/*
try{
    $res = $apiClient->rejudgeSubmit(4053);
    print_r($res);
}catch(Exception $e){
    print_r($e->getMessage());
}
print_r($res);
*/

#getResultAll
/*
try{
    $res = $apiClient->getResultAll(Array(4052));
    foreach($res as $mes){
        print_r($mes->getSubmitId());
        print_r($mes->getSystemStatus());
        print_r($mes->getBuildStatus());
        print_r($mes->getBuildOutput());
        print_r($mes->getTestGroup());
    }
}catch(Exception $e){
    print_r($e->getMessage());
}
*/

#getResult
/*
try{
    $res = $apiClient->getResult(4052);
    print_r($res->getSubmitId());
    print_r($res->getSystemStatus());
    print_r($res->getBuildStatus());
    print_r($res->getBuildOutput());
    print_r($res->getTestGroup());
}catch(Exception $e){
    print_r($e->getMessage());
}
*/


#sendSubmitAll
/*
$submit = new Bacs\model\Submit('D','PING',1);
$all[] = $submit;
$all[] = new Bacs\model\Submit('D','PONG',2);
try{
    $res = $apiClient->sendSubmitAll($all);
    foreach($res as $mes){
        print_r($mes);
    }
}catch(Exception $e){
    print_r($e->getMessage());
}
*/


#sendSubmit
/*
$submit = new Bacs\model\Submit('D','PING',1);
try{
    $res = $apiClient->sendSubmit($submit);
    print_r($res);
}catch(Exception $e){
    print_r($e->getMessage());
}
*/

#getProblems
/*
try{
    $res = $apiClient->getProblems();
    foreach($res as $mes){
        print_r($mes->getId());
        print_r($mes->getInfo());
        print_r($mes->getTimeLimitMillis());
        print_r($mes->getMemoryLimitBytes());
    }
}catch(Exception $e){
    print_r($e->getMessage());
}
*/

#getStatementUrl
/*
try{
    $res = $apiClient->getStatementUrl('A');
    print_r($res);
}catch(Exception $e){
    print_r($e->getMessage());
}
*/

#getAllLanguages
/*
try{
    $res = $apiClient->getAllLanguages();
    foreach($res as $mes){
        print_r($mes->getId());
        print_r($mes->getInfo());
        print_r($mes->getTimeLimitMillis());
        print_r($mes->getMemoryLimitBytes());
        print_r($mes->getCompilerType());
    }
}catch(Exception $e){
    echo $e->getMessage();
}
*/

try{
    $res = $apiClient->Ping();
    var_dump($res);
}catch(Exception $e){
    echo $e->getMessage();
}